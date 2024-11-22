FROM php:8.1-apache

RUN docker-php-ext-install mysqli pdo pdo_mysql calendar

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN apt-get update && \
    apt-get install -y git curl && \
    curl -fsSL https://deb.nodesource.com/setup_16.x | bash - && \
    apt-get install -y nodejs

COPY composer.json composer.lock /var/www/
COPY package.json package-lock.json /var/www/
COPY apache.conf /etc/apache2/sites-enabled/000-default.conf

RUN cd /var/www/ && composer install --no-interaction --prefer-dist --optimize-autoloader

RUN npm install --production

COPY ./app/ /var/www/html/

RUN chown -R www-data:www-data /var/www/html /var/www/node_modules /var/www/vendor && \
    chmod -R 755 /var/www/html /var/www/node_modules /var/www/vendor

EXPOSE 80
