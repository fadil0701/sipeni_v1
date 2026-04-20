# SIMANTIK (Laravel + Apache). Build standalone: docker build -t simantik .
# Compose: ./docker-compose.yml — container simantik-web; host :9001 → container :80.
FROM php:8.3-apache-bookworm

ARG HTTP_PROXY=http://10.15.3.20:80
ARG HTTPS_PROXY=http://10.15.3.20:80
ARG NO_PROXY=localhost,127.0.0.1,mysql,.local

ENV HTTP_PROXY=${HTTP_PROXY} \
    HTTPS_PROXY=${HTTPS_PROXY} \
    NO_PROXY=${NO_PROXY} \
    http_proxy=${HTTP_PROXY} \
    https_proxy=${HTTPS_PROXY} \
    no_proxy=${NO_PROXY}

# Install system dependencies and PHP extensions needed by Laravel.
# Use apt proxy + retries for constrained VM network environments.
RUN printf 'Acquire::http::Proxy "%s";\nAcquire::https::Proxy "%s";\nAcquire::Retries "5";\n' "$HTTP_PROXY" "$HTTPS_PROXY" > /etc/apt/apt.conf.d/01proxy \
    && sed -i 's|http://deb.debian.org|https://deb.debian.org|g' /etc/apt/sources.list.d/debian.sources \
    && apt-get update \
    && apt-get install -y --no-install-recommends \
    libpng-dev \
    libzip-dev \
    libicu-dev \
    libonig-dev \
    && docker-php-ext-install pdo pdo_mysql gd intl zip opcache \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# OPcache: kurangi lambatnya request saat kode di-mount dari Windows (banyak file vendor).
RUN printf '%s\n' \
  'opcache.enable=1' \
  'opcache.memory_consumption=256' \
  'opcache.interned_strings_buffer=16' \
  'opcache.max_accelerated_files=30000' \
  'opcache.validate_timestamps=1' \
  'opcache.revalidate_freq=1' \
  > /usr/local/etc/php/conf.d/opcache.ini

# Enable Apache modules commonly used by Laravel
RUN a2enmod rewrite headers

# Set working directory
WORKDIR /var/www/html

# Copy composer from official image for faster installs (optional optimization)
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copy application source
COPY . /var/www/html

# Install PHP dependencies optimized for production image
RUN composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader

# Ensure storage and bootstrap/cache are writable
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && find /var/www/html -type f -exec chmod 644 {} \; \
    && find /var/www/html -type d -exec chmod 755 {} \;

# Set Laravel document root to public/
RUN sed -i 's#DocumentRoot /var/www/html#DocumentRoot /var/www/html/public#' /etc/apache2/sites-available/000-default.conf \
    && sed -i '/<Directory \/var\/www\/>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf

# Warm up safe caches during build.
# Route cache is intentionally skipped because deployed path prefix
# can differ per environment (/simantik, /, etc).
RUN php artisan config:cache \
    && php artisan view:cache

EXPOSE 80

CMD ["apache2-foreground"]

