FROM php:7.1-apache

ENV WEBBY_DEBUG 0

RUN curl -sL https://deb.nodesource.com/setup_7.x | bash -

RUN apt-get update \
 && apt-get install -y git zlib1g-dev nodejs \
 		libjpeg62-turbo-dev \
 		libpng12-dev \
 && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-configure gd --with-jpeg-dir=/usr/include/ --with-png-dir=/usr/include
RUN docker-php-ext-install opcache zip gd

RUN a2enmod rewrite

RUN npm install -g less

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /var/www/
RUN rm -rf *
COPY ./ ./
RUN composer update
RUN chmod +x bin/system
RUN chown -R www-data:www-data log temp html