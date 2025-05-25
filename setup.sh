#!/bin/bash
set -e
# When starting from scratch, use setup-scaffolding.sh

echo "Setting up Symfony and React application..."
composer install --optimize-autoloader --no-interaction --prefer-dist
bin/console cache:clear
php bin/console doctrine:migrations:migrate --no-interaction
php bin/console app:import-continent-phone-prefix public/sample-data/phone-codes.csv

echo "Creating upload directories..."
# Extract paths from .env file
UPLOAD_PATH=$(grep -E "^UPLOAD_PATH=" .env | cut -d= -f2)
CHUNKS_PATH=$(grep -E "^CHUNKS_PATH=" .env | cut -d= -f2)

# Use default values if not found
if [ -z "$UPLOAD_PATH" ]; then
    UPLOAD_PATH="public/calls-data"
fi

if [ -z "$CHUNKS_PATH" ]; then
    CHUNKS_PATH="${UPLOAD_PATH}/chunks"
fi

# Resolve variable references in CHUNKS_PATH
if [[ "$CHUNKS_PATH" == *'${UPLOAD_PATH}'* ]]; then
    CHUNKS_PATH=${CHUNKS_PATH//\$\{UPLOAD_PATH\}/$UPLOAD_PATH}
fi

# Create directories
echo "Creating directory: $UPLOAD_PATH"
mkdir -p "$UPLOAD_PATH"
echo "Creating directory: $CHUNKS_PATH"
mkdir -p "$CHUNKS_PATH"
echo "Upload directories created."

echo "Building assets..."
yarn add webpack-dev-server --dev
yarn install && yarn dev

echo "Setup complete! Your Symfony application with React is ready."
echo "Access your application at: http://localhost:18080"
echo "Access your React SPA at: http://localhost:18080/app"
