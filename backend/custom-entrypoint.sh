#!/bin/bash
set -e

# Start Apache in the background
apache2-foreground &

# Start queue worker if configured
if [ "$QUEUE_CONNECTION" = "database" ]; then
    echo "Waiting for database connection..."
    while ! php -r 'new PDO("mysql:host=".getenv("DB_HOST").";port=".getenv("DB_PORT").";dbname=".getenv("DB_DATABASE"), getenv("DB_USERNAME"), getenv("DB_PASSWORD"));' 2>/dev/null; do
        sleep 1
    done
    echo "Database connection established! Starting Laravel queue worker in the background..."
    php artisan queue:work --daemon --sleep=3 --tries=3 &
fi

# Wait for Apache to start and trigger the warmup
echo "Starting warmup compilation..."
while ! curl -s http://localhost/warmup.php > /dev/null; do
    sleep 1
done
echo "Warmup compilation completed! Cache is hot."

# Wait for Apache background process to keep the container running
wait
