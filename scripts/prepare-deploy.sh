#!/usr/bin/env bash

#
# Deploy Preparation Script for CEACA CEDOC
# Prepares the site for upload to Hostgator
#
# Usage: ./scripts/prepare-deploy.sh [subdomain-name]
# Example: ./scripts/prepare-deploy.sh cedoc-nova
#

set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(cd "$SCRIPT_DIR/.." && pwd)"
DEPLOY_DIR="${PROJECT_ROOT}/.deploy"
SUBDOMAIN="${1:-cedoc-staging}"

# Color output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

log() {
  printf "${GREEN}[prepare]${NC} %s\n" "$1"
}

warn() {
  printf "${YELLOW}[prepare]${NC} %s\n" "$1"
}

die() {
  printf "${RED}[prepare] ERROR:${NC} %s\n" "$1" >&2
  exit 1
}

# Cleanup from previous run
if [[ -d "$DEPLOY_DIR" ]]; then
  warn "Removing previous deployment directory"
  rm -rf "$DEPLOY_DIR"
fi

log "Creating deployment package for subdomain: $SUBDOMAIN"
mkdir -p "$DEPLOY_DIR"

# Copy WordPress theme
log "Copying Tainacan theme..."
mkdir -p "$DEPLOY_DIR/wp-content/themes"
cp -r "$PROJECT_ROOT/tainacan-theme-master" "$DEPLOY_DIR/wp-content/themes/tainacan-interface"

# Copy scripts
log "Copying deployment scripts..."
mkdir -p "$DEPLOY_DIR/scripts"
cp "$PROJECT_ROOT/scripts/deploy-hostgator.sh" "$DEPLOY_DIR/scripts/"

# Create deployment manifest
log "Creating deployment manifest..."
cat > "$DEPLOY_DIR/MANIFEST.md" <<EOF
# CEACA CEDOC - Deployment Package

**Subdomain**: $SUBDOMAIN
**Created**: $(date)
**Package size**: $(du -sh "$DEPLOY_DIR" | cut -f1)

## Contents
- \`wp-content/themes/tainacan-interface/\` - Custom Tainacan theme for CEDOC
- \`scripts/deploy-hostgator.sh\` - Server-side setup script

## Pre-requisites
- Hostgator cPanel account
- New subdomíno created: \`https://$SUBDOMAIN.ceacac76.hospedagemdesites.ws/\`
- Database created in cPanel (MySQL/MariaDB)
- File manager or FTP access

## Installation Steps

### 1. Upload theme to server
Using FTP or File Manager:
\`\`\`
1. Connect to public_html/$SUBDOMAIN directory
2. Create wp-content/themes directory if not exists
3. Upload tainacan-interface folder
\`\`\`

### 2. Restore database backup
Using phpMyAdmin in cPanel:
\`\`\`
1. Go to phpMyAdmin
2. Select the new database
3. Click Import
4. Choose the file: ceacac76_wp56.sql.gz (from backup)
5. Click Go
\`\`\`

### 3. Update WordPress configuration
Via SSH or File Manager:
\`\`\`
1. Edit wp-config.php in public_html/$SUBDOMAIN
2. Update database credentials:
   - DB_NAME: new database name
   - DB_USER: database user
   - DB_PASSWORD: database password
   - DB_HOST: typically localhost
\`\`\`

### 4. Update WordPress URLs
Using phpMyAdmin in cPanel:
\`\`\`
1. Go to wp_options table
2. Update these records:
   - siteurl: https://$SUBDOMAIN.ceacac76.hospedagemdesites.ws
   - home: https://$SUBDOMAIN.ceacac76.hospedagemdesites.ws
\`\`\`

OR run SSH command (if \`wp-cli\` available):
\`\`\`bash
wp option update siteurl "https://$SUBDOMAIN.ceacac76.hospedagemdesites.ws"
wp option update home "https://$SUBDOMAIN.ceacac76.hospedagemdesites.ws"
\`\`\`

### 5. Verify installation
\`\`\`
1. Visit: https://$SUBDOMAIN.ceacac76.hospedagemdesites.ws
2. Check that pages load correctly
3. Test login at: https://$SUBDOMAIN.ceacac76.hospedagemdesites.ws/wp-admin
\`\`\`

## Troubleshooting

### White screen of death (blank page)
- Check wp-config.php database credentials
- Enable debug in wp-config.php: define('WP_DEBUG', true);
- Check error logs in cPanel > Error Logs

### Database connection error
- Verify MySQL is running
- Check DB credentials match in wp-config.php
- Ensure user has privileges on database

### Theme not loading
- Verify theme uploaded to correct path: wp-content/themes/tainacan-interface
- Check file permissions (should be 755 for folders, 644 for files)
- Clear any caching plugins

## Important Notes
- Do NOT use Docker docker-compose.yml in production
- This is a static WordPress installation
- Keep backups of database before any changes
- Update WordPress and plugins regularly

## Support
For issues, check:
- Hostgator documentation: https://www.hostgator.com.br/suporte/
- WordPress: https://wordpress.org/support/
- Tainacan: https://tainacan.org/
EOF

log "Creating upload instructions..."
cat > "$DEPLOY_DIR/UPLOAD_INSTRUCTIONS.txt" <<'EOF'
═════════════════════════════════════════════════════════════════════════════
  CEACA CEDOC - UPLOAD INSTRUCTIONS FOR HOSTGATOR
═════════════════════════════════════════════════════════════════════════════

FILES TO UPLOAD:

Option 1: Using File Manager (cPanel)
─────────────────────────────────────────────────────────────────────────────
1. Log in to Hostgator cPanel
2. Open File Manager
3. Navigate to public_html directory
4. Create folder: [subdomain-name] (e.g., cedoc-nova)
5. Navigate INTO the [subdomain-name] folder
6. Click "Upload Files" button
7. Select ALL files from wp-content/ folder
8. Wait for upload to complete

Option 2: Using FTP
─────────────────────────────────────────────────────────────────────────────
1. Connect via FTP to your Hostgator account
2. Navigate to: public_html/[subdomain-name]/
3. Upload the entire wp-content folder contents
4. Ensure wp-config.php is in the root (public_html/[subdomain-name]/)
5. Verify permissions are correct

CRITICAL FILES TO UPLOAD:
─────────────────────────────────────────────────────────────────────────────
From backup .tar.gz:
  ✓ wp-config.php (in root)
  ✓ wp-content/themes/tainacan-interface/ (entire folder)
  ✓ wp-content/uploads/ (if you want previous uploads)
  ✓ wp-content/plugins/ (any required plugins)

DATABASE BACKUP TO RESTORE:
─────────────────────────────────────────────────────────────────────────────
File: ceacac76_wp56.sql.gz

Steps:
  1. Create new MySQL database in cPanel > MySQL Databases
  2. Create new MySQL user in cPanel > MySQL Users
  3. Assign user to database with ALL privileges
  4. Go to cPanel > phpMyAdmin
  5. Select the new database
  6. Click "Import" tab
  7. Choose ceacac76_wp56.sql.gz
  8. Click "Go"
  9. Wait for import to complete

AFTER UPLOAD VERIFICATION:
─────────────────────────────────────────────────────────────────────────────
1. Edit wp-config.php and update:
   - DB_NAME to new database name
   - DB_USER to new database user
   - DB_PASSWORD to new database password

2. Via phpMyAdmin, update wp_options:
   - siteurl = https://[subdomain].ceacac76.hospedagemdesites.ws
   - home = https://[subdomain].ceacac76.hospedagemdesites.ws

3. Test by visiting: https://[subdomain].ceacac76.hospedagemdesites.ws

═════════════════════════════════════════════════════════════════════════════
EOF

# Create a checklist
log "Creating pre-deployment checklist..."
cat > "$DEPLOY_DIR/PRE-DEPLOYMENT-CHECKLIST.md" <<EOF
# Pre-Deployment Checklist

## Before Upload

### Hostgator Setup
- [ ] New subdomain created in Hostgator
- [ ] DNS propagated (check with: ping subdomain.ceacac76.hospedagemdesites.ws)
- [ ] New MySQL database created
- [ ] New MySQL user created with privileges
- [ ] FTP/SFTP credentials obtained

### Backup & Testing
- [ ] Local backup of current database taken
- [ ] Theme tested on local environment
- [ ] All custom content verified
- [ ] Links checked for hardcoded paths

### File Preparation
- [ ] Theme copied to deployment package
- [ ] wp-config.php updated with new database credentials
- [ ] .htaccess configured if needed
- [ ] File permissions ready (755 folders, 644 files)

## During Upload

- [ ] Theme uploaded to correct location
- [ ] Database backup imported to new database
- [ ] wp-config.php credentials verified
- [ ] File permissions set correctly

## After Upload

- [ ] Site accessed and homepage loads
- [ ] Admin login works (wp-admin)
- [ ] Collections and items display correctly
- [ ] Search functionality works
- [ ] Social sharing links correct
- [ ] SSL certificate active and working
- [ ] 404 page working correctly

## Post-Deployment

- [ ] Full database backup taken
- [ ] Monitoring set up for errors
- [ ] Weekly backups scheduled
- [ ] Updates tested before applying

EOF

log "✓ Deployment package created: $DEPLOY_DIR"
log "  Size: $(du -sh "$DEPLOY_DIR" | cut -f1)"
log ""
log "Next steps:"
echo "  1. Review contents in: $DEPLOY_DIR"
echo "  2. Read: $DEPLOY_DIR/MANIFEST.md"
echo "  3. Follow: $DEPLOY_DIR/UPLOAD_INSTRUCTIONS.txt"
echo "  4. Use checklist: $DEPLOY_DIR/PRE-DEPLOYMENT-CHECKLIST.md"
echo ""
log "Subdomain configured for: $SUBDOMAIN"
