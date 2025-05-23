#!/bin/bash
set -e

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
        # Install stimulus bundle
        # composer require symfony/stimulus-bundle --no-interaction
        # Fix the PHPStan PhpDocParser dependency issue by updating symfony/property-info
        composer require --with-all-dependencies "symfony/property-info:^7.0.8"
        composer clear-cache
        composer install --optimize-autoloader
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
        # Install stimulus bundle
        # composer require symfony/stimulus-bundle --no-interaction
        # Fix the PHPStan PhpDocParser dependency issue by updating symfony/property-info
        composer require --with-all-dependencies "symfony/property-info:^7.0.8"
        composer clear-cache
        composer install --optimize-autoloader
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
else
    echo "Symfony project already exists, updating dependencies..."
    composer install
    # Install stimulus bundle if not already installed
    # composer require symfony/stimulus-bundle --no-interaction
    # Fix the PHPStan PhpDocParser dependency issue by updating symfony/property-info
    composer require --with-all-dependencies "symfony/property-info:^7.0.8"
    composer clear-cache
    composer install --optimize-autoloader
    bin/console cache:clear
    php bin/console doctrine:migrations:migrate --no-interaction
    php bin/console app:import-continent-phone-prefix public/sample-data/phone-codes.csv

fi

# Set up React in the assets directory
if [ ! -d "assets/react" ]; then
    echo "Setting up React application..."
    mkdir -p assets/react
    yarn add @babel/preset-react --dev
    yarn add react react-dom prop-types axios
    yarn add @hotwired/stimulus @symfony/stimulus-bridge

    # Create basic React app structure
    mkdir -p assets/react/components

    # Create main React entry point
    cat > assets/react/app.js << 'EOL'
import React from 'react';
import ReactDOM from 'react-dom';
import App from './components/App';

ReactDOM.render(<App />, document.getElementById('root'));
EOL

    # Create basic App component
    cat > assets/react/components/App.js << 'EOL'
import React from 'react';

const App = () => {
    return (
        <div className="container mt-5">
            <div className="row">
                <div className="col-md-12">
                    <div className="card">
                        <div className="card-header">
                            <h3>Symfony with React</h3>
                        </div>
                        <div className="card-body">
                            <p>Your Symfony and React application is now set up!</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
};

export default App;
EOL

    # Update webpack.config.js to include React
    sed -i "/Encore.setOutputPath/a Encore.enableReactPreset()" webpack.config.js
    # React app entry is now handled directly in webpack.config.js
fi

# Create a controller for the SPA if it doesn't exist
if [ ! -f "src/Controller/SpaController.php" ]; then
    echo "Creating SPA controller..."
    mkdir -p src/Controller
    cat > src/Controller/SpaController.php << 'EOL'
<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SpaController extends AbstractController
{
    /**
     * @Route("/app/{reactRouting}", name="app", defaults={"reactRouting": null}, requirements={"reactRouting"=".+"})
     */
    public function index(): Response
    {
        return $this->render('spa/index.html.twig');
    }
}
EOL

    # Create the template for the SPA
    mkdir -p templates/spa
    cat > templates/spa/index.html.twig << 'EOL'
{% extends 'base.html.twig' %}

{% block title %}React App{% endblock %}

{% block stylesheets %}
    {{ parent() }}
    {{ encore_entry_link_tags('app') }}
{% endblock %}

{% block body %}
    <div id="root"></div>
{% endblock %}

{% block javascripts %}
    {{ parent() }}
    {{ encore_entry_script_tags('app') }}
{% endblock %}
EOL
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
# Ensure Stimulus packages are installed
yarn add @hotwired/stimulus @symfony/stimulus-bridge
yarn add webpack-dev-server --dev
yarn install && yarn dev

echo "Setup complete! Your Symfony application with React is ready."
echo "Access your application at: http://localhost:18080"
echo "Access your React SPA at: http://localhost:18080/app"
#sleep 10000
