#!/bin/bash
set -e

# This version was used to scaffold a new project.
# When there is a git project repo, after git clone <project.git>,
# setup.sh is used.
# Main purpose of creating this file is to save for scaffolding,
# but to remove scaffolding code from setup.sh

echo "Setting up Symfony and React application..."

# Create Symfony project if it doesn't exist
if [ ! -f "composer.json" ]; then
    echo "Creating new Symfony project..."
    # Check if directory is empty (excluding hidden files)
    if [ -z "$(ls -A | grep -v '^\.')" ]; then
        echo "Directory is empty, creating Symfony project using Symfony CLI..."
        # Use Symfony CLI to create a new project (without Git initialization)
        symfony new . --webapp --version=lts --no-git
        # Install webpack encore bundle
        composer require symfony/webpack-encore-bundle
        composer install --optimize-autoloader --no-interaction --prefer-dist
        bin/console cache:clear
        bin/console cache:clear
        php bin/console doctrine:migrations:migrate --no-interaction
        php bin/console app:import-continent-phone-prefix public/sample-data/phone-codes.csv
    else
        echo "Directory is not empty, creating Symfony project using Symfony CLI..."
        # Create a temporary directory
        mkdir -p /tmp/symfony-project
        cd /tmp/symfony-project
        # Use Symfony CLI to create a new project in the temporary directory (without Git initialization)
        symfony new . --webapp --version=lts --no-git
        # Install webpack encore bundle
        composer require symfony/webpack-encore-bundle
        composer install --optimize-autoloader --no-interaction --prefer-dist
        bin/console cache:clear
        bin/console cache:clear
        php bin/console doctrine:migrations:migrate --no-interaction
        php bin/console app:import-continent-phone-prefix public/sample-data/phone-codes.csv

        # Copy the files to the current directory
        cp -r * /app/
        cp -r .env /app/
        cp -r .env.local /app/ 2>/dev/null || true
        cp -r .env.test /app/ 2>/dev/null || true

        # Go back to the original directory
        cd /app
    fi
fi

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
