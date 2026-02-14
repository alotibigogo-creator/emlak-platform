FROM php:8.3-cli

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git curl zip unzip libzip-dev libpng-dev libonig-dev libxml2-dev libsqlite3-dev \
    && docker-php-ext-install pdo pdo_sqlite mbstring zip gd xml bcmath \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Copy composer files first for caching
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-scripts

# Copy application
COPY . .

# Run post-install scripts
RUN composer dump-autoload --optimize

# Create SQLite database
RUN mkdir -p database && touch database/database.sqlite

# Set permissions
RUN chmod -R 775 storage bootstrap/cache database

# Cache config
RUN php artisan config:clear \
    && php artisan route:cache \
    && php artisan view:cache

EXPOSE 8080

CMD php artisan migrate --force && php artisan db:seed --class=AdminUserSeeder --force && php artisan serve --host=0.0.0.0 --port=8080
