#!/bin/bash

# Correct Laravel project path
PROJECT_PATH="/home/u235777426/domains/norloxsolutionscrm.com/public_html"

# Full PHP binary path (verify with `which php`)
PHP_PATH="/usr/bin/php"

# Ensure logs folder exists
mkdir -p "$PROJECT_PATH/storage/logs"

# Infinite loop to keep timers running
while true; do
    echo "[$(date)] Starting timers:decrement process..." | tee -a "$PROJECT_PATH/storage/logs/timer_decrement.log"

    cd "$PROJECT_PATH" || { echo "Failed to cd into $PROJECT_PATH"; exit 1; }

    # Run the artisan command
    $PHP_PATH artisan timers:decrement >> "$PROJECT_PATH/storage/logs/timer_decrement.log" 2>&1

    echo "[$(date)] Process crashed or stopped. Restarting in 5 seconds..." | tee -a "$PROJECT_PATH/storage/logs/timer_decrement.log"
    sleep 5
done
