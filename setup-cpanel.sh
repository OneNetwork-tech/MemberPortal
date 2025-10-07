#!/bin/bash
# Setup script for cPanel deployment

echo "Setting up MEMBERPORTAL on cPanel..."

# Create necessary directories
mkdir -p ~/membership.onenetwork.se
mkdir -p ~/logs

# Set permissions
chmod 755 ~/membership.onenetwork.se
chmod 644 ~/membership.onenetwork.se/*.php
chmod 755 ~/membership.onenetwork.se/assets/

# Create environment file
cat > ~/membership.onenetwork.se/.env << EOF
DB_HOST=localhost
DB_NAME=onenetwo_memberportal
DB_USER=onenetwo_memberportal
DB_PASS=Anjina@1985
BASE_URL=https://membership.onenetwork.se/
ENVIRONMENT=production
EOF

chmod 600 ~/membership.onenetwork.se/.env

echo "Setup complete!"