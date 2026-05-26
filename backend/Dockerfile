FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
    git unzip libpng-dev libjpeg-dev libfreetype6-dev libzip-dev libicu-dev libwebp-dev \
    && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp

RUN docker-php-ext-install pdo pdo_mysql mysqli gd intl zip \
    && a2enmod rewrite

# Поднимаем лимиты на размер загружаемых файлов
RUN printf 'upload_max_filesize = 10M\npost_max_size = 10M\nmemory_limit = 256M\n' > /usr/local/etc/php/conf.d/uploads.ini

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
WORKDIR /var/www/html
COPY backend /var/www/html/backend
RUN cd /var/www/html/backend && composer install --no-dev --optimize-autoloader

RUN sed -ri -e 's!/var/www/html!/var/www/html/backend/web!g' /etc/apache2/sites-available/000-default.conf
RUN mkdir -p /var/www/html/backend/web/uploads && chown -R www-data:www-data /var/www/html/backend/runtime /var/www/html/backend/web/uploads

# Entrypoint: при старте контейнера выставляем права на bind-mount папки
RUN printf '#!/bin/sh\nchown -R www-data:www-data /var/www/html/backend/web/uploads /var/www/html/backend/runtime 2>/dev/null || true\nexec apache2-foreground\n' > /usr/local/bin/start.sh \
    && chmod +x /usr/local/bin/start.sh

CMD ["/usr/local/bin/start.sh"]
EXPOSE 80