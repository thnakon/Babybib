#!/bin/bash

# =====================================================
# Babybib - Restore Backup Script
# =====================================================
# Usage:
#   ./restore_backup.sh                    # List available backups
#   ./restore_backup.sh backup_file.sql.gz # Restore specific backup
# =====================================================

# Configuration - CHANGE THESE VALUES FOR YOUR SERVER
DB_HOST="localhost"
DB_NAME="babybib_db"
DB_USER="root"
DB_PASS=""

# Paths
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
BACKUP_DIR="$SCRIPT_DIR/../backups"

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
CYAN='\033[0;36m'
NC='\033[0m'

# MySQL command
MYSQL_CMD="mysql"
if [[ -f "/Applications/XAMPP/xamppfiles/bin/mysql" ]]; then
    MYSQL_CMD="/Applications/XAMPP/xamppfiles/bin/mysql"
fi

# If no argument, list available backups
if [[ -z "$1" ]]; then
    echo -e "${CYAN}Available backups:${NC}"
    echo "----------------------------------------"
    
    if [[ -d "$BACKUP_DIR" ]]; then
        ls -lh "$BACKUP_DIR"/*.sql.gz 2>/dev/null | while read line; do
            echo "$line"
        done
        
        COUNT=$(ls "$BACKUP_DIR"/*.sql.gz 2>/dev/null | wc -l)
        if [[ $COUNT -eq 0 ]]; then
            echo -e "${YELLOW}No backups found in $BACKUP_DIR${NC}"
        fi
    else
        echo -e "${RED}Backup directory not found: $BACKUP_DIR${NC}"
    fi
    
    echo "----------------------------------------"
    echo -e "Usage: ${GREEN}./restore_backup.sh <backup_file.sql.gz>${NC}"
    exit 0
fi

BACKUP_FILE="$1"

# Check if file exists
if [[ ! -f "$BACKUP_DIR/$BACKUP_FILE" ]]; then
    if [[ -f "$BACKUP_FILE" ]]; then
        BACKUP_PATH="$BACKUP_FILE"
    else
        echo -e "${RED}Error: Backup file not found: $BACKUP_FILE${NC}"
        exit 1
    fi
else
    BACKUP_PATH="$BACKUP_DIR/$BACKUP_FILE"
fi

echo -e "${YELLOW}⚠️  WARNING: This will REPLACE all data in database '$DB_NAME'${NC}"
echo -e "Backup file: $BACKUP_PATH"
read -p "Are you sure you want to continue? (yes/no): " CONFIRM

if [[ "$CONFIRM" != "yes" ]]; then
    echo "Restore cancelled."
    exit 0
fi

echo -e "${GREEN}Starting restore...${NC}"

# Decompress and restore
if [[ "$BACKUP_PATH" == *.gz ]]; then
    if [[ -z "$DB_PASS" ]]; then
        gunzip -c "$BACKUP_PATH" | $MYSQL_CMD -h "$DB_HOST" -u "$DB_USER" "$DB_NAME"
    else
        gunzip -c "$BACKUP_PATH" | $MYSQL_CMD -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME"
    fi
else
    if [[ -z "$DB_PASS" ]]; then
        $MYSQL_CMD -h "$DB_HOST" -u "$DB_USER" "$DB_NAME" < "$BACKUP_PATH"
    else
        $MYSQL_CMD -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" < "$BACKUP_PATH"
    fi
fi

if [[ $? -eq 0 ]]; then
    echo -e "${GREEN}✓ Database restored successfully!${NC}"
else
    echo -e "${RED}✗ Restore failed!${NC}"
    exit 1
fi
