FROM php:7.1.23-apache

ENV WEBBY_DEBUG 0

WORKDIR /var/www/

RUN apt-get update \
  && apt-get install -y gnupg \
  && curl -sL https://deb.nodesource.com/setup_10.x | bash - \
  && apt-get install -y \
    git \
    zlib1g-dev \
    nodejs \
 		libjpeg62-turbo-dev \
 		libpng-dev \
  && rm -rf /var/lib/apt/lists/* \
  && docker-php-ext-configure gd --with-jpeg-dir=/usr/include/ --with-png-dir=/usr/include \
  && docker-php-ext-install opcache zip gd \
  && a2enmod rewrite expires headers \
  && npm install -g less --no-progress \
  && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
  && rm -rf *

COPY . .

RUN composer install --no-progress \
  && chmod +x bin/system \
  && chown -R www-data:www-data log temp html

COPY docker-webby-run /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-webby-run
ENTRYPOINT ["docker-webby-run"]
CMD ["apache2-foreground"]
