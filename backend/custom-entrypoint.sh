#!/bin/bash
set -e

# Start Apache in the background
apache2-foreground &

# Wait for Apache to start and trigger the warmup
echo "Starting warmup compilation..."
while ! curl -s http://localhost/warmup.php > /dev/null; do
    sleep 1
done
echo "Warmup compilation completed! Cache is hot."

# Wait for Apache background process to keep the container running
wait
