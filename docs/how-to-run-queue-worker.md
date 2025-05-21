# Running the Queue Worker for Processing Uploaded Files

After uploading a CSV file through the UI, the file is saved and a message is dispatched to the queue for asynchronous processing. This application uses a dedicated Docker service to process these queued messages automatically.

## Using the Docker Worker Service

The application includes a dedicated worker service in the Docker Compose configuration that automatically processes the queue. This service is configured to:

- Run the Symfony Messenger queue worker
- Process messages from the 'async' transport
- Automatically restart if it crashes (using Docker's `restart: unless-stopped` policy)
- Limit memory usage to prevent resource issues
- Restart periodically to prevent memory leaks

## Starting the Worker

The worker service starts automatically when you start the Docker environment:

```bash
docker-compose up -d
```

This command starts all services, including the dedicated worker service that processes the queue.

## Viewing Worker Logs

To check if the worker is running properly and view its logs:

```bash
docker-compose logs -f worker
```

This command shows the logs from the worker service and follows new log entries as they are generated.

## Worker Configuration

The worker service is configured in the `docker-compose.yml` file:

```yaml
# Queue worker service
worker:
  build:
    context: .
    dockerfile: docker/php/Dockerfile
  container_name: exam_worker_container
  working_dir: /app
  user: appuser
  restart: unless-stopped
  command: >
    sh -c "sudo chown -R appuser:appuser /app && \
     bin/console messenger:consume async --time-limit=3600 --memory-limit=128M"
  volumes:
    - ./:/app
  depends_on:
    - database
  environment:
    DATABASE_URL: mysql://symfony:symfony@database:3306/symfony?serverVersion=8.0
```

## Monitoring the Queue

To check the status of the queue and see how many messages are pending, you can run:

```bash
docker-compose exec php bin/console messenger:stats
```

## Handling Failed Messages

If a message fails to be processed, it will be sent to the 'failed' transport. You can retry failed messages with:

```bash
docker-compose exec php bin/console messenger:failed:retry
```

## Troubleshooting

If messages are not being processed, check:

1. That the worker service is running: `docker-compose ps`
2. The worker logs for any errors: `docker-compose logs worker`
3. That the transport is correctly configured in `config/packages/messenger.yaml`
4. That the `MESSENGER_TRANSPORT_DSN` environment variable is correctly set in `.env`
