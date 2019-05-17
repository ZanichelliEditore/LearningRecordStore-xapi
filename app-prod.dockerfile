FROM php:7-fpm

ARG USER

RUN apt-get update &&  apt-get install -y libmcrypt-dev mysql-client openssl zip unzip git libpng-dev libjpeg62-turbo-dev libgd-dev  apt-utils \
    && docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ \
    && docker-php-ext-install gd 

RUN docker-php-ext-install pdo_mysql

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

ADD ./ /var/www
RUN chown -R www-data:www-data /var/www
RUN chmod 777 -R /var/www/storage
RUN chmod 777 -R /var/www/vendor
WORKDIR /var/www
USER www-data



