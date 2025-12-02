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
    /var/www/storage/database \
    /var/www/bootstrap/cache && \
    touch /var/www/storage/database/diary.sqlite && \
    chown -R www-data:www-data /var/www && \
    chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# Install composer dependencies
RUN composer install --no-dev --optimize-autoloader

# Create startup script with migrations
RUN echo '#!/bin/bash\n\
set -e\n\
\n\
# Force SQLite configuration\n\
export DB_CONNECTION=sqlite\n\
export DB_DATABASE=/var/www/storage/database/diary.sqlite\n\
\n\
# Ensure SQLite database exists and has correct permissions\n\
mkdir -p /var/www/storage/database\n\
touch /var/www/storage/database/diary.sqlite\n\
chown -R www-data:www-data /var/www/storage\n\
chmod -R 775 /var/www/storage\n\
\n\
# Clear cached config\n\
php artisan config:clear\n\
php artisan route:clear\n\
php artisan view:clear\n\
php artisan cache:clear || true\n\
\n\
# Debug: show DB config\n\
echo "DB_CONNECTION: $DB_CONNECTION"\n\
echo "DB_DATABASE: $DB_DATABASE"\n\
\n\
# Run migrations\n\
echo "Running migrations..."\n\
php artisan migrate --force\n\
\n\
echo "Starting services..."\n\
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
