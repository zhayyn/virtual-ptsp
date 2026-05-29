# ============================================================
# Virtual PTSP - Docker Image
# Built with ❤️ by zhayyn (+6281317361689)
# ============================================================

FROM php:8.3-fpm-alpine

# ============================================================
# Install System Dependencies
# ============================================================
RUN apk add --no-cache \
    bash \
    curl \
    libpng-dev \
    libxml2-dev \
    zip \
    unzip \
    git \
    oniguruma-dev \
    libzip-dev \
    supervisor \
    cron \
    linux-headers \
    $PHPIZE_DEPS

# ============================================================
# PHP Extensions
# ============================================================
RUN docker-php-ext-install \
    pdo \
    pdo_mysql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    zip \
    intl \
    opcache \
    redis

# ============================================================
# Redis Extension
# ============================================================
RUN pecl install redis && docker-php-ext-enable redis

# ============================================================
# Composer
# ============================================================
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# ============================================================
# Nginx & PHP-FPM Configuration
# ============================================================
COPY docker/nginx/virtual-ptsp.conf /etc/nginx/conf.d/default.conf
COPY docker/php/php.ini /usr/local/etc/php/php.ini
COPY docker/supervisor.conf /etc/supervisord.conf

# ============================================================
# Directory Setup
# ============================================================
RUN mkdir -p /var/www/html/storage/logs \
    && mkdir -p /var/www/html/storage/framework/{cache,sessions,views} \
    && mkdir -p /var/www/html/storage/app/public \
    && mkdir -p /var/www/html/bootstrap/cache \
    && mkdir -p /var/www/html/node_modules \
    && mkdir -p /var/www/html/public \
    && mkdir -p /var/run \
    && chown -R www-data:www-data /var/www/html

WORKDIR /var/www/html

# ============================================================
# Copy Application Files (will be mounted as volume in compose)
# Copy dummy first, real app will be copied during build
# ============================================================
COPY src/ /var/www/html/

# ============================================================
# Permissions
# ============================================================
RUN chown -R www-data:www-data /var/www/html/storage \
    && chown -R www-data:www-data /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage \
    && chmod -R 775 /var/www/html/bootstrap/cache

# ============================================================
# Entrypoint Script
# ============================================================
COPY docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

ENTRYPOINT ["/entrypoint.sh"]

# ============================================================
# Start Services
# ============================================================
CMD ["php-fpm"]

# ============================================================
# Labels
# ============================================================
LABEL maintainer="zhayyn (+6281317361689)" \
      product="Virtual PTSP" \
      description="Omnichannel Customer Service Platform with AI" \
      url="https://virtual-ptsp.com"