#!/bin/bash
set -e

chmod +x /app/setup.sh
/app/setup.sh

# Execute the command passed to the container
exec "$@"