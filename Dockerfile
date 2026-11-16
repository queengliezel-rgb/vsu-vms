FROM php:8.2-apache
RUN docker-php-ext-install pdo pdo_mysql
COPY . /var/www/html/
RUN mkdir -p uploads && chmod -R 777 uploads
