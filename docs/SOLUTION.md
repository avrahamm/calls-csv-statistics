# Solution: Running the Queue Worker for Processing Uploaded Files

## Issue Description

After uploading a CSV file through the UI, the file was successfully uploaded to the server, but it wasn't being processed automatically. This is because the application uses Symfony Messenger for asynchronous processing, and the queue worker needs to be started manually to process the messages in the queue.

## Implemented Solution

To address this issue, I've implemented a dedicated worker service in the Docker Compose configuration:

1. **Docker Worker Service**: Added a new service to `docker-compose.yml` that runs the Symfony Messenger queue worker:
   - Based on the same PHP image as the main application
   - Configured to run the `messenger:consume` command
   - Set with appropriate memory and time limits
   - Automatically starts when the Docker environment is launched
   - Runs independently from the main PHP application

## How to Use

### Quick Start

To start processing uploaded files, simply start your Docker environment:

```bash
docker-compose up -d
```

This will start all services, including the dedicated worker service that processes the queue.

### For Production

The worker service is already configured with sensible defaults for production use:

1. **Memory Limit**: The worker is configured with a memory limit of 128MB to prevent memory leaks from affecting the system.

2. **Time Limit**: The worker will automatically restart after 1 hour (3600 seconds) to prevent potential issues with long-running processes.

3. **Automatic Restart**: The service uses Docker's `restart: unless-stopped` policy, which ensures that if the worker crashes or stops for any reason, Docker will automatically restart it.

4. **Independent Operation**: The worker runs as a separate service, so it won't affect the performance of the main application.

## Technical Details

The application uses Symfony Messenger with the following configuration:

- Transport: Doctrine (configured in `.env` with `MESSENGER_TRANSPORT_DSN=doctrine://default?auto_setup=0`)
- Message: `ProcessUploadedFileMessage`
- Handler: `ProcessUploadedFileMessageHandler`
- Routing: Messages are routed to the 'async' transport in `config/packages/messenger.yaml`

When a file is uploaded, the `CallsController` dispatches a `ProcessUploadedFileMessage` to the message bus, which routes it to the 'async' transport. The queue worker processes these messages by invoking the `ProcessUploadedFileMessageHandler`, which uses the `CallsCsvProcessor` service to process the CSV file and insert the data into the database.

## Next Steps

Consider implementing one of the following improvements:

1. Set up a health check for the worker service to ensure it's processing messages correctly
2. Implement a monitoring solution to track the status of the queue and worker
3. Configure Docker Compose to use a more sophisticated orchestration tool like Docker Swarm or Kubernetes for production environments
