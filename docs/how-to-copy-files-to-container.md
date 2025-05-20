# How to Copy Files to the exam_php_container

This document explains how to copy files from your host machine to the `exam_php_container` Docker container and where to place different types of files.

## Understanding the Docker Setup

The project uses Docker for containerization with several services defined in the `docker-compose.yml` file:

- `php` service (container name: `exam_php_container`): Runs PHP-FPM for the Symfony application
- `nginx` service: Serves as the web server
- `database` service: Runs MySQL database
- `node` service: Provides Node.js for React development

## Methods to Copy Files to the Container

There are several ways to copy files to the `exam_php_container`:

### 1. Using the Volume Mapping (Recommended)

The Docker Compose configuration already includes a volume mapping from your host machine to the container:

```yaml
volumes:
  - ./:/app
```

This means that the entire project directory on your host machine is mapped to the `/app` directory in the container. **Any files you place in the project directory on your host will automatically be available in the container.**

This is the simplest and recommended method because:
- No extra commands are needed
- Changes are immediately reflected
- You can edit files using your preferred editor on the host

### 2. Using Docker CP Command

If you need to copy a file that's outside your project directory, you can use the `docker cp` command:

```bash
#docker cp /path/to/your/file.ext exam_php_container:/app/destination/path/
docker cp public/sample-data/phone-codes.csv exam_php_container:/app/destination/path/
```

For example, to copy a CSV file to the `var` directory:

```bash
docker cp /path/to/your/data.csv exam_php_container:/app/var/
```

### 3. Using SCP (for Remote Hosts)

If you're copying from a remote host, you can use `scp` to first copy to your local machine, then use one of the methods above.

## Where to Place Different Types of Files

Depending on the type of file, you should place it in the appropriate directory within the project:

### Data Files (CSV, JSON, etc.)

- **Location**: `/app/var/`
- **Purpose**: Store variable data files that are not part of the source code
- **Example**: `docker cp data.csv exam_php_container:/app/var/`

### Configuration Files

- **Location**: `/app/config/`
- **Purpose**: Store application configuration
- **Example**: Place in `config/` directory on host

### Source Code Files (PHP)

- **Location**: `/app/src/`
- **Purpose**: Store PHP source code
- **Example**: Place in `src/` directory on host

### Public Assets

- **Location**: `/app/public/`
- **Purpose**: Store publicly accessible files
- **Example**: Place in `public/` directory on host

### Frontend Assets

- **Location**: `/app/assets/`
- **Purpose**: Store frontend assets (JS, CSS, etc.)
- **Example**: Place in `assets/` directory on host

### Templates

- **Location**: `/app/templates/`
- **Purpose**: Store Twig templates
- **Example**: Place in `templates/` directory on host

## Example: Copying and Using a CSV File

1. **On your host machine**, place your CSV file in the project's `var` directory:
   ```bash
   cp /path/to/your/data.csv var/
   ```

2. **In the container**, the file will be available at `/app/var/data.csv`

3. **In your PHP code**, you can reference it as:
   ```php
   $csvFilePath = __DIR__ . '/../var/data.csv';
   ```

## Permissions

The container runs as the `appuser` user (UID 1000), so ensure that the files you copy have appropriate permissions:

```bash
# If needed, adjust permissions on your host
chmod 644 var/your-file.csv
```

## Verifying File Placement

To verify that your file has been correctly placed in the container:

```bash
docker exec exam_php_container ls -la /app/var/
```

This will list all files in the `/app/var/` directory inside the container.