#!/bin/bash

# Always build .env from the current container environment so runtime values
# (SMTP, recipients, webhook URLs, etc.) override any stale values baked into the image.
if [ -f "/app/.env.example" ]; then
  envsubst < /app/.env.example > /app/.env
fi

# Create the database file
if [ ! -f "/app/db.sqlite" ]; then
  touch /app/db.sqlite
  chown www-data:www-data /app/db.sqlite
fi

# Run PHP preparation commands
php artisan migrate --force

php artisan key:generate

# Set permissions for logging folder
chmod -R 700 /app/storage

# Start supervisord and services
exec /usr/bin/supervisord -n -c /etc/supervisord.conf
