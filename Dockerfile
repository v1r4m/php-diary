FROM php:8.2-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libpq-dev \
    zip \
    unzip \
    libzip-dev \
    nginx

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_pgsql pgsql mbstring exif pcntl bcmath gd zip

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy existing application directory contents
COPY . /var/www

# Remove local .env to use Render's environment variables
RUN rm -f /var/www/.env

# Copy nginx configuration for single-container (Render) deployment
COPY docker/nginx/nginx-render.conf /etc/nginx/sites-available/default
RUN ln -sf /etc/nginx/sites-available/default /etc/nginx/sites-enabled/default

# Create required directories and set permissions
RUN mkdir -p /var/www/storage/framework/sessions \
    /var/www/storage/framework/views \
    /var/www/storage/framework/cache \
    /var/www/storage/logs \
    /var/www/bootstrap/cache && \
    chown -R www-data:www-data /var/www && \
    chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# Install composer dependencies
RUN composer install --no-dev --optimize-autoloader

# Create startup script with migrations
RUN echo '#!/bin/bash\n\
set -e\n\
\n\
# Clear cached config to use runtime env vars\n\
php artisan config:clear\n\
php artisan route:clear\n\
php artisan view:clear\n\
php artisan cache:clear || true\n\
\n\
# Debug: show database connection info\n\
echo "DATABASE_URL set: ${DATABASE_URL:+yes}"\n\
\n\
# Wait for database to be ready\n\
echo "Waiting for database..."\n\
for i in $(seq 1 30); do\n\
  php artisan migrate:status > /dev/null 2>&1 && break\n\
  echo "Attempt $i: Database not ready, waiting..."\n\
  sleep 2\n\
done\n\
\n\
# Run migrations\n\
php artisan migrate --force\n\
\n\
# Start PHP-FPM in background\n\
php-fpm -D\n\
\n\
# Start nginx in foreground\n\
nginx -g "daemon off;"\n\
' > /start.sh && chmod +x /start.sh

# Expose port for Render
EXPOSE 8080

# For local Docker Compose
EXPOSE 9000

# Default command (will be overridden by docker-compose or Render)
CMD ["/start.sh"]
