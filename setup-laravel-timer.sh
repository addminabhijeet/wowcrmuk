#!/bin/bash

# ------------------------------
# Laravel Timer Systemd Setup
# ------------------------------

# Set your Laravel project path
PROJECT_PATH="/var/www/norloxsolutionscrm.com/wowcrm"
SERVICE_NAME="laravel-timer.service"
USER_NAME="root"  # change if running as another user
PHP_BIN="/usr/bin/php"  # path to PHP CLI

echo "reating logs folder if it doesn't exist..."
mkdir -p "$PROJECT_PATH/storage/logs"

echo "reating systemd service file..."
SERVICE_FILE="/etc/systemd/system/$SERVICE_NAME"

cat <<EOL | sudo tee $SERVICE_FILE
[Unit]
Description=Laravel Timer Decrement Service
After=network.target

[Service]
Type=simple
User=$USER_NAME
WorkingDirectory=$PROJECT_PATH
ExecStart=$PHP_BIN artisan timers:decrement
Restart=always
RestartSec=2
StandardOutput=file:$PROJECT_PATH/storage/logs/timers.log
StandardError=file:$PROJECT_PATH/storage/logs/timers-error.log

[Install]
WantedBy=multi-user.target
EOL

echo "eloading systemd daemon..."
sudo systemctl daemon-reload

echo "nabling service to start on boot..."
sudo systemctl enable $SERVICE_NAME

echo "tarting Laravel timer service..."
sudo systemctl start $SERVICE_NAME

echo " Laravel timer service setup completed!"
echo "You can check status with:"
echo "   sudo systemctl status $SERVICE_NAME"
echo "Logs will be available in:"
echo "   $PROJECT_PATH/storage/logs/timers.log"
echo "   $PROJECT_PATH/storage/logs/timers-error.log"

