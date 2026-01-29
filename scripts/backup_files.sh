#!/bin/bash

# =====================================================
# Babybib - Files Backup Script
# =====================================================
# Backs up uploads and important files
# Usage: ./backup_files.sh
# =====================================================

# Paths
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_DIR="$SCRIPT_DIR/.."
BACKUP_DIR="$PROJECT_DIR/backups"
LOG_FILE="$PROJECT_DIR/logs/backup.log"

# Settings
DATE=$(date +%Y%m%d_%H%M%S)
KEEP_DAYS=30

# Colors
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

log() {
    local message="[$(date '+%Y-%m-%d %H:%M:%S')] $1"
    echo "$message" >> "$LOG_FILE"
    echo -e "$message"
}

mkdir -p "$BACKUP_DIR"

log "${GREEN}Starting files backup...${NC}"

# Backup uploads folder
if [[ -d "$PROJECT_DIR/uploads" ]]; then
    UPLOADS_BACKUP="uploads_${DATE}.tar.gz"
    tar -czf "$BACKUP_DIR/$UPLOADS_BACKUP" -C "$PROJECT_DIR" uploads 2>/dev/null
    
    if [[ -f "$BACKUP_DIR/$UPLOADS_BACKUP" ]]; then
        SIZE=$(du -h "$BACKUP_DIR/$UPLOADS_BACKUP" | cut -f1)
        log "✓ Uploads backup: $UPLOADS_BACKUP ($SIZE)"
    fi
fi

# Backup configuration files
CONFIG_BACKUP="config_${DATE}.tar.gz"
tar -czf "$BACKUP_DIR/$CONFIG_BACKUP" \
    -C "$PROJECT_DIR" \
    includes/config.php \
    includes/email-config.php \
    .htaccess \
    2>/dev/null

if [[ -f "$BACKUP_DIR/$CONFIG_BACKUP" ]]; then
    SIZE=$(du -h "$BACKUP_DIR/$CONFIG_BACKUP" | cut -f1)
    log "✓ Config backup: $CONFIG_BACKUP ($SIZE)"
fi

# Clean old backups
find "$BACKUP_DIR" -name "uploads_*.tar.gz" -mtime +$KEEP_DAYS -delete
find "$BACKUP_DIR" -name "config_*.tar.gz" -mtime +$KEEP_DAYS -delete

log "${GREEN}Files backup complete!${NC}"
log "----------------------------------------"
