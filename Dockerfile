FROM php:8.2-cli

WORKDIR /app

RUN apt-get update && apt-get install -y \
    git unzip zip libzip-dev libicu-dev \
 && docker-php-ext-install zip intl \
 && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copia o projeto inteiro antes, para o Composer enxergar o classmap
COPY . .

RUN composer install --no-interaction --prefer-dist

EXPOSE 8765

CMD ["php", "bin/cake.php", "server", "-H", "0.0.0.0", "-p", "8765"]
