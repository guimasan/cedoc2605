#!/usr/bin/env bash

#
# Hostgator Deployment Script for CEACA CEDOC
# Execute this on the Hostgator server after uploading files
#
# Usage: ssh user@server 'bash ./scripts/deploy-hostgator.sh [options]'
#

set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(cd "$SCRIPT_DIR/.." && pwd)"

# Color output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

log() {
  printf "${GREEN}[deploy]${NC} %s\n" "$1"
}

info() {
  printf "${BLUE}[deploy]${NC} %s\n" "$1"
}

warn() {
  printf "${YELLOW}[deploy]${NC} %s\n" "$1"
}

die() {
  printf "${RED}[deploy] ERROR:${NC} %s\n" "$1" >&2
  exit 1
}

# Check if running on server
if [[ ! -f "$PROJECT_ROOT/wp-load.php" && ! -f "$PROJECT_ROOT/wp-config.php" ]]; then
  die "WordPress files not found in: $PROJECT_ROOT"
fi

log "Starting Hostgator deployment..."

# Check file permissions
log "Checking file permissions..."
warn "Setting directory permissions to 755..."
find "$PROJECT_ROOT" -type d ! -perm 755 -exec chmod 755 {} \; 2>/dev/null || true

warn "Setting file permissions to 644..."
find "$PROJECT_ROOT" -type f ! -perm 644 -exec chmod 644 {} \; 2>/dev/null || true

# Special permissions
log "Setting special permissions..."
chmod 700 "$PROJECT_ROOT/wp-config.php" || warn "Could not set wp-config.php to 700"
chmod 755 "$PROJECT_ROOT/wp-content" || warn "Could not set wp-content to 755"
chmod 755 "$PROJECT_ROOT/wp-content/uploads" || warn "Could not set uploads to 755"

# Create necessary directories if missing
log "Ensuring required directories exist..."
mkdir -p "$PROJECT_ROOT/wp-content/uploads"
mkdir -p "$PROJECT_ROOT/wp-content/plugins"
mkdir -p "$PROJECT_ROOT/wp-content/themes"

# Check WordPress configuration
log "Verifying WordPress configuration..."
if [[ ! -f "$PROJECT_ROOT/wp-config.php" ]]; then
  die "wp-config.php not found! Make sure to upload it first."
fi

# Try to load WordPress
if [[ -f "$PROJECT_ROOT/wp-load.php" ]]; then
  log "WordPress files detected. Ready for configuration."
else
  warn "WordPress core files may not be completely uploaded"
fi

# Check theme
THEME_DIR="$PROJECT_ROOT/wp-content/themes/tainacan-interface"
if [[ -d "$THEME_DIR" ]]; then
  log "✓ Theme found: tainacan-interface"
  log "  Location: $THEME_DIR"
else
  warn "Theme not found at expected location"
  warn "Please ensure tainacan-interface is in: wp-content/themes/"
fi

# Summary
log ""
log "Deployment preparation completed!"
log ""
log "Next manual steps in cPanel:"
echo "  1. Go to phpMyAdmin"
echo "  2. Select your new database"
echo "  3. Click 'Import' and choose: ceacac76_wp56.sql.gz"
echo "  4. After import, execute this command via SSH:"
echo ""
echo "    mysql -u[DB_USER] -p[DB_PASSWORD] [DB_NAME] -e \\"
echo "      UPDATE wp_options SET option_value='https://[SUBDOMAIN].ceacac76.hospedagemdesites.ws' \\"
echo "      WHERE option_name IN ('siteurl', 'home');\""
echo ""
echo "  5. Visit: https://[SUBDOMAIN].ceacac76.hospedagemdesites.ws"
echo ""
log "Files and permissions are ready!"
