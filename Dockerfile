FROM php:8.4-cli-alpine

RUN apk add --no-cache postgresql-dev libzip-dev nodejs npm \
    && docker-php-ext-install pdo pdo_pgsql zip

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader --no-scripts

COPY package.json package-lock.json ./
RUN npm ci --ignore-scripts || npm install --ignore-scripts

COPY . .

RUN npm run build
RUN composer dump-autoload --optimize

EXPOSE 8080

CMD ["sh", "-c", "php -S 0.0.0.0:${PORT:-8080} -t public public/index.php"]
