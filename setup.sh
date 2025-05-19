#!/bin/bash
set -e

echo "Setting up Symfony and React application..."

# Create Symfony project if it doesn't exist
if [ ! -f "composer.json" ]; then
    echo "Creating new Symfony project..."
    docker-compose exec php composer create-project symfony/website-skeleton .
    docker-compose exec php composer require symfony/webpack-encore-bundle
else
    echo "Symfony project already exists, updating dependencies..."
    docker-compose exec php composer install
fi

# Set up React in the assets directory
if [ ! -d "assets/react" ]; then
    echo "Setting up React application..."
    docker-compose exec php mkdir -p assets/react
    docker-compose exec node bash -c "cd /var/www/html && yarn add @babel/preset-react --dev"
    docker-compose exec node bash -c "cd /var/www/html && yarn add react react-dom prop-types axios"

    # Create basic React app structure
    docker-compose exec php mkdir -p assets/react/components

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
    docker-compose exec php sed -i "/Encore.setOutputPath/a Encore.enableReactPreset()" webpack.config.js
    docker-compose exec php sed -i "/Encore.addEntry/a Encore.addEntry('react_app', './assets/react/app.js')" webpack.config.js
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
    {{ encore_entry_link_tags('react_app') }}
{% endblock %}

{% block body %}
    <div id="root"></div>
{% endblock %}

{% block javascripts %}
    {{ parent() }}
    {{ encore_entry_script_tags('react_app') }}
{% endblock %}
EOL
fi

echo "Building assets..."
docker-compose exec node bash -c "cd /var/www/html && yarn install && yarn encore dev"

echo "Setup complete! Your Symfony application with React is ready."
echo "Access your application at: http://localhost:18080"
echo "Access your React SPA at: http://localhost:18080/app"
