#!/bin/bash

# =====================================================
# Babybib - Database Backup Script
# =====================================================
# Usage:
#   ./backup_database.sh           # Manual backup
#   ./backup_database.sh --cron    # Cron job mode (quiet)
#
# Cron Example (daily at 2:00 AM):
#   0 2 * * * /path/to/babybib/scripts/backup_database.sh --cron
# =====================================================

# Configuration - CHANGE THESE VALUES FOR YOUR SERVER
DB_HOST="localhost"
DB_NAME="babybib_db"
DB_USER="root"
DB_PASS=""                    # Leave empty if no password

# Paths
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
BACKUP_DIR="$SCRIPT_DIR/../backups"
LOG_FILE="$SCRIPT_DIR/../logs/backup.log"

# Backup settings
KEEP_DAYS=30                  # Keep backups for 30 days
DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_FILE="babybib_db_${DATE}.sql"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Check if running in cron mode
QUIET_MODE=false
if [[ "$1" == "--cron" ]]; then
    QUIET_MODE=true
fi

# Logging function
log() {
    local message="[$(date '+%Y-%m-%d %H:%M:%S')] $1"
    echo "$message" >> "$LOG_FILE"
    if [[ "$QUIET_MODE" == false ]]; then
        echo -e "$message"
    fi
}

# Ensure backup directory exists
mkdir -p "$BACKUP_DIR"
mkdir -p "$(dirname "$LOG_FILE")"

# Start backup
log "${GREEN}Starting database backup...${NC}"
log "Database: $DB_NAME"
log "Backup file: $BACKUP_FILE"

# Build mysqldump command
MYSQL_CMD="mysqldump"

# For XAMPP on Mac
if [[ -f "/Applications/XAMPP/xamppfiles/bin/mysqldump" ]]; then
    MYSQL_CMD="/Applications/XAMPP/xamppfiles/bin/mysqldump"
fi

# Execute backup
if [[ -z "$DB_PASS" ]]; then
    $MYSQL_CMD -h "$DB_HOST" -u "$DB_USER" "$DB_NAME" > "$BACKUP_DIR/$BACKUP_FILE" 2>> "$LOG_FILE"
else
    $MYSQL_CMD -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" > "$BACKUP_DIR/$BACKUP_FILE" 2>> "$LOG_FILE"
fi

# Check if backup was successful
if [[ $? -eq 0 && -s "$BACKUP_DIR/$BACKUP_FILE" ]]; then
    # Compress the backup
    gzip "$BACKUP_DIR/$BACKUP_FILE"
    FINAL_FILE="${BACKUP_FILE}.gz"
    FILE_SIZE=$(du -h "$BACKUP_DIR/$FINAL_FILE" | cut -f1)
    
    log "${GREEN}✓ Backup successful!${NC}"
    log "  File: $FINAL_FILE"
    log "  Size: $FILE_SIZE"
    
    # Clean up old backups
    DELETED=$(find "$BACKUP_DIR" -name "babybib_db_*.sql.gz" -type f -mtime +$KEEP_DAYS -delete -print | wc -l)
    if [[ $DELETED -gt 0 ]]; then
        log "${YELLOW}Cleaned up $DELETED old backup(s)${NC}"
    fi
    
    # Show total backup count
    TOTAL=$(find "$BACKUP_DIR" -name "babybib_db_*.sql.gz" | wc -l)
    log "Total backups: $TOTAL"
    
else
    log "${RED}✗ Backup failed!${NC}"
    log "Check the log file for errors: $LOG_FILE"
    exit 1
fi

log "----------------------------------------"
