#!/usr/bin/env bash

set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(cd "$SCRIPT_DIR/.." && pwd)"
ARCHIVE_DEFAULT="$PROJECT_ROOT/backup-5.5.2026_11-17-33_ceacac76.tar.gz"
LOCAL_SITE_URL_DEFAULT="http://localhost:8080"
DB_NAME_DEFAULT="tainacan"
DB_USER_DEFAULT="tainacan"
DB_PASSWORD_DEFAULT="tainacan"
BACKUP_PREFIX_DEFAULT="wpii_"
LOCAL_PREFIX_DEFAULT="wp_"

usage() {
  cat <<'EOF'
Usage:
  scripts/restore-hostgator-backup.sh --apply [--archive PATH] [--local-url URL]

What it does:
  - extracts the HostGator cPanel backup
  - converts table prefix wpii_ -> wp_
  - imports the MySQL dump into the local Docker database
  - copies wp-content/uploads and wp-content/plugins/wpforms-lite into WordPress
  - updates home/siteurl and replaces the old domain with the local URL

Safety:
  - this script backs up the current local database before touching it
  - it drops existing wp_ tables before importing the backup
EOF
}

log() {
  printf '[restore] %s\n' "$1"
}

die() {
  printf '[restore] ERROR: %s\n' "$1" >&2
  exit 1
}

require_cmd() {
  command -v "$1" >/dev/null 2>&1 || die "missing required command: $1"
}

docker_compose() {
  docker compose "$@"
}

APPLY=0
ARCHIVE="$ARCHIVE_DEFAULT"
LOCAL_SITE_URL="$LOCAL_SITE_URL_DEFAULT"
DB_NAME="$DB_NAME_DEFAULT"
DB_USER="$DB_USER_DEFAULT"
DB_PASSWORD="$DB_PASSWORD_DEFAULT"
BACKUP_PREFIX="$BACKUP_PREFIX_DEFAULT"
LOCAL_PREFIX="$LOCAL_PREFIX_DEFAULT"

while [[ $# -gt 0 ]]; do
  case "$1" in
    --apply)
      APPLY=1
      shift
      ;;
    --archive)
      ARCHIVE="${2:-}"
      shift 2
      ;;
    --local-url)
      LOCAL_SITE_URL="${2:-}"
      shift 2
      ;;
    --db-name)
      DB_NAME="${2:-}"
      shift 2
      ;;
    --db-user)
      DB_USER="${2:-}"
      shift 2
      ;;
    --db-password)
      DB_PASSWORD="${2:-}"
      shift 2
      ;;
    --backup-prefix)
      BACKUP_PREFIX="${2:-}"
      shift 2
      ;;
    --local-prefix)
      LOCAL_PREFIX="${2:-}"
      shift 2
      ;;
    -h|--help)
      usage
      exit 0
      ;;
    *)
      die "unknown argument: $1"
      ;;
  esac
done

if [[ "$APPLY" -ne 1 ]]; then
  usage
  exit 1
fi

require_cmd docker
require_cmd tar
require_cmd sed
require_cmd mktemp

[[ -f "$ARCHIVE" ]] || die "backup archive not found: $ARCHIVE"

COMPOSE_DIR="$PROJECT_ROOT"
TMP_ROOT="$(mktemp -d)"
STAGE_ROOT="$PROJECT_ROOT/.hostgator-restore"
SQL_STAGE="$STAGE_ROOT/backup.sql"
SQL_IMPORTED="$STAGE_ROOT/backup.wp.sql"
CURRENT_DB_BACKUP="$STAGE_ROOT/current-db-$(date +%Y%m%d-%H%M%S).sql"

ARCHIVE_LISTING="$TMP_ROOT/archive-listing.txt"
tar -tzf "$ARCHIVE" > "$ARCHIVE_LISTING"
BACKUP_BASE="$(sed -n '1s#/.*##p' "$ARCHIVE_LISTING")"
[[ -n "$BACKUP_BASE" ]] || die "could not determine backup base folder inside archive"

SQL_SOURCE="$BACKUP_BASE/mysql/ceacac76_wp56.sql"
UPLOADS_SOURCE="$BACKUP_BASE/homedir/public_html/wp-content/uploads"
PLUGINS_SOURCE="$BACKUP_BASE/homedir/public_html/wp-content/plugins/wpforms-lite"

log "extracting backup archive"
mkdir -p "$STAGE_ROOT"
tar -xzf "$ARCHIVE" -C "$TMP_ROOT" \
  "$SQL_SOURCE" \
  "$UPLOADS_SOURCE" \
  "$PLUGINS_SOURCE" \
  >/dev/null 2>&1 || true

[[ -f "$TMP_ROOT/$SQL_SOURCE" ]] || die "sql dump not found after extraction"
[[ -d "$TMP_ROOT/$UPLOADS_SOURCE" ]] || die "uploads folder not found after extraction"
[[ -d "$TMP_ROOT/$PLUGINS_SOURCE" ]] || die "wpforms-lite plugin folder not found after extraction"

log "backing up current local database"
docker_compose -f "$COMPOSE_DIR/docker-compose.yml" exec -T db \
  sh -lc "mysqldump -u'$DB_USER' -p'$DB_PASSWORD' '$DB_NAME'" > "$CURRENT_DB_BACKUP"

log "dropping current wp_ tables"
CURRENT_TABLES="$(docker_compose -f "$COMPOSE_DIR/docker-compose.yml" exec -T db \
  sh -lc "mysql -u'$DB_USER' -p'$DB_PASSWORD' -Nse \"SHOW TABLES LIKE '${LOCAL_PREFIX}\\_%';\" '$DB_NAME'" | tr -d '\r' || true)"
if [[ -n "$CURRENT_TABLES" ]]; then
  DROP_SQL="SET FOREIGN_KEY_CHECKS=0;"
  while IFS= read -r table_name; do
    [[ -n "$table_name" ]] || continue
    DROP_SQL+="DROP TABLE IF EXISTS \`$table_name\`;"
  done <<< "$CURRENT_TABLES"
  DROP_SQL+="SET FOREIGN_KEY_CHECKS=1;"
  printf '%s\n' "$DROP_SQL" | docker_compose -f "$COMPOSE_DIR/docker-compose.yml" exec -T db \
    sh -lc "mysql -u'$DB_USER' -p'$DB_PASSWORD' '$DB_NAME'"
fi

log "preparing sql dump with local prefix"
sed \
  -e "s/${BACKUP_PREFIX}/${LOCAL_PREFIX}/g" \
  -e '/^CREATE DATABASE /d' \
  -e '/^USE /d' \
  -e '/^-- Current Database:/d' \
  "$TMP_ROOT/$SQL_SOURCE" > "$SQL_IMPORTED"

log "importing restored database"
docker_compose -f "$COMPOSE_DIR/docker-compose.yml" exec -T db \
  sh -lc "mysql -u'$DB_USER' -p'$DB_PASSWORD' '$DB_NAME'" < "$SQL_IMPORTED"

log "copying uploads into WordPress container"
docker_compose -f "$COMPOSE_DIR/docker-compose.yml" exec -T wordpress sh -lc 'mkdir -p /var/www/html/wp-content/uploads && rm -rf /var/www/html/wp-content/uploads/*'
tar -C "$TMP_ROOT/$UPLOADS_SOURCE" -cf - . | docker_compose -f "$COMPOSE_DIR/docker-compose.yml" exec -T wordpress sh -lc 'tar -C /var/www/html/wp-content/uploads -xpf -'

log "copying wpforms-lite plugin into WordPress container"
docker_compose -f "$COMPOSE_DIR/docker-compose.yml" exec -T wordpress sh -lc 'mkdir -p /var/www/html/wp-content/plugins/wpforms-lite && rm -rf /var/www/html/wp-content/plugins/wpforms-lite/*'
tar -C "$TMP_ROOT/$PLUGINS_SOURCE" -cf - . | docker_compose -f "$COMPOSE_DIR/docker-compose.yml" exec -T wordpress sh -lc 'tar -C /var/www/html/wp-content/plugins/wpforms-lite -xpf -'

log "replacing old domain with local url inside WordPress data"
docker_compose -f "$COMPOSE_DIR/docker-compose.yml" exec -T wordpress env LOCAL_SITE_URL="$LOCAL_SITE_URL" php <<'PHP'
<?php
require '/var/www/html/wp-load.php';

global $wpdb;

$local_url = getenv('LOCAL_SITE_URL') ?: 'http://localhost:8080';
$searches = array('https://ceacacedoc.com.br', 'http://ceacacedoc.com.br');

function cedoc_restore_replace_recursive($value, string $search, string $replace) {
    if (is_string($value)) {
        return str_replace($search, $replace, $value);
    }

    if (is_array($value)) {
        foreach ($value as $key => $child) {
            $value[$key] = cedoc_restore_replace_recursive($child, $search, $replace);
        }
        return $value;
    }

    if (is_object($value)) {
        foreach (get_object_vars($value) as $key => $child) {
            $value->$key = cedoc_restore_replace_recursive($child, $search, $replace);
        }
        return $value;
    }

    return $value;
}

function cedoc_restore_update_table_column(WPDB $wpdb, string $table, string $id_column, string $data_column, array $searches, string $replace): void {
    $rows = $wpdb->get_results("SELECT {$id_column}, {$data_column} FROM {$table}", ARRAY_A);

    foreach ($rows as $row) {
        $original = $row[$data_column];
        $value = maybe_unserialize($original);

        foreach ($searches as $search) {
            $value = cedoc_restore_replace_recursive($value, $search, $replace);
        }

        $value = maybe_serialize($value);

        if ($value !== $original) {
            $wpdb->update(
                $table,
                array($data_column => $value),
                array($id_column => $row[$id_column]),
                array('%s'),
                array('%d')
            );
        }
    }
}

update_option('home', $local_url);
update_option('siteurl', $local_url);
update_option('upload_path', '');
update_option('upload_url_path', '');

cedoc_restore_update_table_column($wpdb, $wpdb->options, 'option_id', 'option_value', $searches, $local_url);
cedoc_restore_update_table_column($wpdb, $wpdb->posts, 'ID', 'post_content', $searches, $local_url);
cedoc_restore_update_table_column($wpdb, $wpdb->posts, 'ID', 'post_excerpt', $searches, $local_url);
cedoc_restore_update_table_column($wpdb, $wpdb->posts, 'ID', 'guid', $searches, $local_url);
cedoc_restore_update_table_column($wpdb, $wpdb->postmeta, 'meta_id', 'meta_value', $searches, $local_url);
cedoc_restore_update_table_column($wpdb, $wpdb->usermeta, 'umeta_id', 'meta_value', $searches, $local_url);
cedoc_restore_update_table_column($wpdb, $wpdb->termmeta, 'meta_id', 'meta_value', $searches, $local_url);

echo "Updated site URLs to {$local_url}\n";
PHP

log "restore complete"
log "current db backup saved at: $CURRENT_DB_BACKUP"
log "local uploads restored and URLs updated"

rm -rf "$TMP_ROOT"
