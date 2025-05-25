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

## Getting Started In short:
```bash
git clone https://github.com/avrahamm/calls-csv-statistics.git calls2
cd calls2/
cp .env.example .env
# edit IP_GEOLOCATION_API_KEY
# Now you can run
docker compose -f docker-compose.yml up --build
# You should be able to open an app http://localhost:18080/app
# And browse by menu upload and statistics pages.
# Cleanup
docker compose down -v --remove-orphans
docker rmi calls2-worker:latest calls2-php:latest
```

## Getting Started in details:

### 1. Clone the Repository

```bash
git clone <repository-url>
cd <repository-directory>
```

### 2. Start the Docker Containers

For first-time setup or after modifying the Dockerfile:
```bash
docker compose -f docker-compose.yml build
docker compose -f docker-compose.yml up -d
```

Or, for a single command that builds (if needed) and starts the containers:
```bash
docker compose -f docker-compose.yml up -d --build
```

For the later starts when no Dockerfile changes have been made:
```bash
docker compose -f docker-compose.yml up -d
```

This will start all the necessary services:
- PHP (Symfony)
- Worker (Message Queue Consumer)
- MySQL
- Nginx
- Node.js

### 3. Run the Setup Script

For a new installation after cloning the repository:

```bash
./setup.sh
```

This script will:
- Install Composer dependencies
- Clear Symfony cache
- Run database migrations
- Import continent phone prefixes from sample data
- Create upload directories for call data processing
- Build the assets

If you're starting from scratch and need to scaffold a new project:

```bash
./setup-scaffolding.sh
```

This script will:
- Create a new Symfony project (if it doesn't exist)
- Install Symfony Webpack Encore bundle
- Set up React in the Symfony assets directory
- Create a basic React application structure
- Configure Webpack Encore for React
- Create a Symfony controller for the SPA
- Run database migrations
- Import continent phone prefixes from sample data
- Create upload directories for call data processing
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

## Development Workflow/ Code Syncing

This Docker development/not production setup uses volume mappings to ensure 
that any changes you make to the source code on your host machine 
are immediately synced to the containers. This means:

- You can edit code in your favorite IDE/editor on your host machine
- Changes are instantly available in the containers without 
- requiring rebuilds or restarts
- This applies to all source code files 
- (PHP, JavaScript, CSS, Twig templates, etc.)

The volume mappings are defined in the `docker-compose.yml` 

#### Excluded Directories

To improve performance and reduce disk usage, the following directories are not synced between the host and containers:

- `vendor/`: Composer dependencies are kept inside the containers only
- `node_modules/`: NPM/Yarn dependencies are kept inside the containers only

These directories use named volumes in Docker, which means:
- They persist between container restarts
- They don't consume space on your host machine
- They don't slow down your development environment with unnecessary file syncing

Additionally, the following files and directories are excluded 
from the Docker build context via `.dockerignore`:

- Symfony cache, logs, and sessions: `var/cache/`, `var/log/`, `var/sessions/`
- Webpack build output: `public/build/`
- Development tools: `.git/`, `.github/`, `.idea/`, `.vscode/`
- Docker configuration: `docker/`, `docker-compose*.yml`, `compose*.yaml`
- Environment and configuration files: `.env.local`, `.env.*.local`
- Testing files: `.phpunit.result.cache`, `phpunit.xml`, `tests/`
- Temporary and system files: `*.log`, `*.cache`, `*.swp`, `*.swo`, `.DS_Store`
- doc folder and .md files.
This ensures that only the necessary files are included
- in the Docker build context, resulting in faster builds
- and smaller images.

### Symfony Development

The Symfony application code is located in the project root.
Any changes to the PHP files will be immediately available
in the container and reflected in the browser after a page refresh.

### React Development

The React application is located in the `assets/react` directory. While the source files are immediately synced to the container, you need to rebuild the assets for the changes to take effect in the browser:

```bash
docker compose exec node bash -c "cd /app && yarn encore dev"
```

For automatic rebuilding during development (recommended):
```bash
docker compose exec node bash -c "cd /app && yarn encore dev --watch"
```

This will watch for changes in your React files 
and automatically rebuild the assets whenever a file is modified.

### Git Versioning

This project is designed to use Git for version control
on the host machine, not inside the Docker containers.
The `.git` directory is already excluded from the Docker build context
via `.dockerignore`, which ensures:

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

### Environment Variables

The application uses several environment variables that can be configured in the `.env` file:

#### Docker Container Configuration
- `PHP_CONTAINER_NAME`: Name for the PHP container (default: calls2-php-container)
- `WORKER_CONTAINER_NAME`: Name for the worker container (default: calls2-worker-container)
- `NGINX_CONTAINER_NAME`: Name for the Nginx container (default: calls2-nginx-container)
- `DATABASE_CONTAINER_NAME`: Name for the database container (default: calls2-database-container)
- `NODE_CONTAINER_NAME`: Name for the Node.js container (default: calls2-node-container)

#### Port Mappings
- `NGINX_PORT`: Port for accessing the web application (default: 18080)
- `DATABASE_PORT`: Port for accessing the database from the host (default: 13306)
- `PHP_PORT`: Port for PHP-FPM (default: 9000)

#### Worker Settings
- `WORKER_TIME_LIMIT`: Time limit for the worker process in seconds (default: 3600)
- `WORKER_MEMORY_LIMIT`: Memory limit for the worker process (default: 128M)

#### Database Settings
- `MYSQL_ROOT_PASSWORD`: Root password for MySQL (default: root)
- `MYSQL_DATABASE`: Database name (default: symfony)
- `MYSQL_USER`: Database user (default: symfony)
- `MYSQL_PASSWORD`: Database password (default: symfony)
- `DOCKER_DATABASE_URL`: Database connection URL for Docker environment

#### Upload Paths
- `UPLOAD_PATH`: Directory for uploaded CSV files (default: public/calls-data)
- `CHUNKS_PATH`: Directory for temporary chunk files (default: ${UPLOAD_PATH}/chunks)

#### Enrichment Settings ans steps
- `ENRICH_DEST_CONTINENT_OFFSET`: Offset for destination 
- continent enrichment (default: 10)
- `IP_GEOLOCATION_API_KEY`: API key for IP geolocation service
- `ENRICH_SOURCE_CONTINENT_OFFSET`: Offset for source continent enrichment (default: 10)

#### Enrichment steps
- EnrichDestContinentMessageHandler serves batches (default: 10)
    of unique source dialed numbers. 
- EnrichSourceContinentMessageHandler serves batches (default: 10)
    of source ip values with an API calls 
    are made only for one ip at a time.
    Batch look is possible for but needs a paid subscription.
    two previous handlers are done.

### PHP Configuration

PHP configuration can be modified in `docker/php/php.ini`.

### Nginx Configuration

Nginx configuration can be modified in `docker/nginx/default.conf.template`.

### Docker Configuration

Docker services can be customized in the `docker-compose.yml` file (or `compose.yaml` in newer versions).

## Troubleshooting

### Permission Issues

If you encounter permission issues, you may need to adjust the permissions of the project directory:

```bash
sudo chown -R $USER:$USER .
```

### Database Connection Issues

If the application cannot connect to the database, ensure that:
1. The database container is running: `docker compose ps`
2. The DATABASE_URL in the .env file matches the configuration in docker-compose.yml (or compose.yaml)
3. The database has been created: `docker compose exec database mysql -u symfony -psymfony -e "SHOW DATABASES;"`

## Parallel CSV Processing

This application includes an optimized system for processing large CSV files containing call data. The system uses parallel processing to significantly improve performance when handling large datasets.

### Overview

The parallel processing approach follows these key steps:

1. **File Splitting**: Large CSV files are split into smaller chunks using the Linux `split` utility
2. **Parallel Processing**: Each chunk is processed independently and concurrently
3. **Staging Table**: Data is first written to a staging table for validation
4. **Final Commit**: Valid data is transferred to the final table in a single transaction
5. **Cleanup**: Temporary files and staging data are removed after processing

### Architecture Components

#### Environment Configuration

The system uses the following environment variables:
- `UPLOAD_PATH`: Directory for uploaded CSV files
- `CHUNKS_PATH`: Directory for temporary chunk files (set to `${UPLOAD_PATH}/chunks`)

#### File Splitting Process

The `ParallelCallsCsvProcessor` service:
- Generates a unique batch ID for each import operation
- Splits the input CSV file into chunks of 15 lines each using the `split` command
- Creates a unique prefix for chunk filenames to avoid conflicts

#### Message-Based Architecture

The system uses Symfony Messenger for asynchronous processing:
- `ProcessUploadedFileChunkMessage`: Dispatched for each chunk file
- `FinalizeCallsImportMessage`: Dispatched after all chunks are queued for processing
- Messages are processed by dedicated handlers that can run in parallel

The worker service in the Docker setup is responsible for consuming these messages:
```bash
# From docker-compose.yml
worker:
  # ... other configuration ...
  command: ["bin/console", "messenger:consume", "async", "--time-limit=${WORKER_TIME_LIMIT}", "--memory-limit=${WORKER_MEMORY_LIMIT}"]
```

You can configure the worker's time and memory limits using the `WORKER_TIME_LIMIT` and `WORKER_MEMORY_LIMIT` environment variables.

#### Staging Table Approach

The `calls_staging` table:
- Mirrors the structure of the final `calls` table
- Includes additional columns for tracking:
  - `batch_id`: Unique identifier for the import operation
  - `chunk_filename`: Name of the chunk file
  - `row_number_in_chunk`: Position of the row in the chunk
  - `is_valid`: The Flag indicating if the row passed validation
  - `error_message`: Details of any validation errors

#### Error Handling and Validation

Each row undergoes validation:
- Data type checking
- Required field validation
- Format validation (e.g., valid IP addresses)
- Invalid rows are marked in the staging table 
but don't prevent processing of valid rows
- If any invalid rows are found, the entire batch is rejected 
during finalization

#### Finalization Process

The `FinalizeCallsImportMessageHandler`:
- Checks if all rows in the staging table are valid
- Transfers valid data to the final `calls` table in a single transaction
- Updates the status of the uploaded file record
- Cleans up the staging table and temporary chunk files
- Dispatches messages for enriching continent data

### Usage

To process a CSV file using parallel processing:

```bash
php bin/console app:process-calls-csv-parallel path/to/file.csv
```

### Benefits

- **Improved Performance**: By avoiding serial operations 
- and performing operations in bulk or parallel manner as much as possible.
- **Improved Performance**: Processing large files in 
- parallel significantly reduces total processing time
- **Scalability**: The system can scale to handle 
- large files by adjusting the chunk size
- **Reliability**: Transaction-based approach ensures data integrity
- **Input file error handling **: In case a corrupted chunk is detected,
- the file is not processed.
- **Detailed Error Reporting**: Precise tracking of which rows had issues and why

## License

[MIT License](LICENSE)
