FROM php:7.2-apache

ENV DEBIAN_FRONTEND noninteractive

# Configure Apache and installs other services
RUN a2enmod rewrite \
    && apt-get update \
    && apt-get install -y curl git libxml2-dev \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN docker-php-ext-install xml
RUN docker-php-ext-install mysqli
RUN docker-php-ext-install pdo_mysql


RUN a2enmod rewrite