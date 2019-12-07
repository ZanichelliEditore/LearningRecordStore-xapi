FROM php:7.2-fpm

ARG USER
ARG UID

RUN apt-get update &&  apt-get install -y libmcrypt-dev mariadb-client openssl zip unzip git libpng-dev libjpeg62-turbo-dev libgd-dev  apt-utils \
    && docker-php-ext-install pdo_mysql \
    && pecl install xdebug-2.7.0beta1 \
    && docker-php-ext-enable xdebug 

RUN mkdir -p /home/$USER
RUN groupadd -g $UID $USER
RUN useradd -u $UID -g $USER $USER -d /home/$USER
RUN chown $USER:$USER /home/$USER
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /var/www
USER $USER
