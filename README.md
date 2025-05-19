# Symfony with React Docker Setup

This repository contains a Docker setup for developing a Symfony application with a React single-page application (SPA).

## Features

- PHP 8.1 with all extensions required for Symfony
- MySQL 8.0 database
- Nginx web server
- Node.js for React development
- Automatic setup script for Symfony and React
- Live code syncing between host and containers for immediate development

## Requirements

- Docker
- Docker Compose

## Getting Started

### 1. Clone the Repository

```bash
git clone <repository-url>
cd <repository-directory>
```

### 2. Start the Docker Containers

```bash
docker-compose up -d
```

This will start all the necessary services:
- PHP (Symfony)
- MySQL
- Nginx
- Node.js

### 3. Run the Setup Script

```bash
./setup.sh
```

This script will:
- Create a new Symfony project (if it doesn't exist)
- Set up React in the Symfony assets directory
- Create a basic React application structure
- Configure Webpack Encore for React
- Create a Symfony controller for the SPA
- Build the assets

### 4. Access the Application

- Symfony application: http://localhost:18080
- React SPA: http://localhost:18080/app

## Database Connection

The MySQL database is accessible with the following credentials:
- Host: localhost
- Port: 13306
- Database: symfony
- Username: symfony
- Password: symfony

Inside the Docker network, the database host is `database` instead of `localhost`.

## Development Workflow

### Code Syncing

This Docker setup uses volume mappings to ensure that any changes you make to the source code on your host machine are immediately synced to the containers. This means:

- You can edit code in your favorite IDE/editor on your host machine
- Changes are instantly available in the containers without requiring rebuilds or restarts
- This applies to all source code files (PHP, JavaScript, CSS, Twig templates, etc.)

The volume mappings are defined in the `docker-compose.yml` file, where the project root directory is mounted to `/var/www/html` in each container.

#### Excluded Directories

To improve performance and reduce disk usage, the following directories are not synced between the host and containers:

- `vendor/`: Composer dependencies are kept inside the containers only
- `node_modules/`: NPM/Yarn dependencies are kept inside the containers only

These directories use named volumes in Docker, which means:
- They persist between container restarts
- They don't consume space on your host machine
- They don't slow down your development environment with unnecessary file syncing

Additionally, the following files and directories are excluded from the Docker build context via `.dockerignore`:

- Symfony cache, logs, and sessions: `var/cache/`, `var/log/`, `var/sessions/`
- Webpack build output: `public/build/`
- Development tools: `.git/`, `.github/`, `.idea/`, `.vscode/`
- Docker configuration: `docker/`, `docker-compose*.yml`
- Environment and configuration files: `.env.local`, `.env.*.local`
- Testing files: `.phpunit.result.cache`, `phpunit.xml`, `tests/`
- Temporary and system files: `*.log`, `*.cache`, `*.swp`, `*.swo`, `.DS_Store`

This ensures that only the necessary files are included in the Docker build context, resulting in faster builds and smaller images.

### Symfony Development

The Symfony application code is located in the project root. Any changes to the PHP files will be immediately available in the container and reflected in the browser after a page refresh.

### React Development

The React application is located in the `assets/react` directory. While the source files are immediately synced to the container, you need to rebuild the assets for the changes to take effect in the browser:

```bash
docker-compose exec node bash -c "cd /var/www/html && yarn encore dev"
```

For automatic rebuilding during development (recommended):
```bash
docker-compose exec node bash -c "cd /var/www/html && yarn encore dev --watch"
```

This will watch for changes in your React files and automatically rebuild the assets whenever a file is modified.

### Git Versioning

This project is designed to use Git for version control on the host machine, not inside the Docker containers. The `.git` directory is already excluded from the Docker build context via `.dockerignore`, which ensures:

- Faster builds and smaller Docker images
- No Git-related operations inside the containers
- Clean separation between application code and version control

#### Git Setup

If you're starting a new project and Git is not yet initialized:

```bash
# Initialize Git repository
git init

# Add all files to Git
git add .

# Create initial commit
git commit -m "Initial commit"
```

If you want to connect to a remote repository:

```bash
# Add remote repository
git remote add origin <remote-repository-url>

# Push to remote repository
git push -u origin main
```

#### Git Workflow

For day-to-day development:

1. Make changes to the code on your host machine
2. Test changes using the Docker environment
3. Commit changes to Git:
   ```bash
   git add .
   git commit -m "Description of changes"
   ```
4. Push changes to the remote repository:
   ```bash
   git pull  # Always pull first to avoid conflicts
   git push
   ```

All Git operations should be performed on the host machine, not inside the Docker containers.

## Customizing the Setup

### PHP Configuration

PHP configuration can be modified in `docker/php/php.ini`.

### Nginx Configuration

Nginx configuration can be modified in `docker/nginx/default.conf`.

### Docker Configuration

Docker services can be customized in the `docker-compose.yml` file.

## Troubleshooting

### Permission Issues

If you encounter permission issues, you may need to adjust the permissions of the project directory:

```bash
sudo chown -R $USER:$USER .
```

### Database Connection Issues

If the application cannot connect to the database, ensure that:
1. The database container is running: `docker-compose ps`
2. The DATABASE_URL in the .env file matches the configuration in docker-compose.yml
3. The database has been created: `docker-compose exec database mysql -u symfony -psymfony -e "SHOW DATABASES;"`

## License

[MIT License](LICENSE)
