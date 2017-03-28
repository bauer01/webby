FROM php:7.1

RUN apt-get update \
 && apt-get install -y git zlib1g-dev \
 && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install zip

RUN curl -sL https://deb.nodesource.com/setup_7.x | bash - && \
  apt-get install -y nodejs

RUN npm install -g less

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

COPY . /usr/src/app
WORKDIR /usr/src/app

RUN composer update
RUN chmod +x bin/system
RUN php bin/system install

ENTRYPOINT ["php", "vendor/bin/tester"]
CMD ["tests/unit", "-p", "php"]