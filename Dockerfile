# Dockerfile
FROM php:8.5-cli
RUN apt-get update && apt-get install -y --no-install-recommends \
      git unzip libicu-dev libzip-dev \
 && docker-php-ext-install -j"$(nproc)" intl zip pdo_mysql \
 && rm -rf /var/lib/apt/lists/*
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
ENV COMPOSER_ALLOW_SUPERUSER=1
WORKDIR /app
EXPOSE 8765
CMD ["php", "bin/cake.php", "server", "-H", "0.0.0.0", "-p", "8765"]
