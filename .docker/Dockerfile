FROM php:8.3.9-apache

ENV COMPOSER_ALLOW_SUPERUSER=1

EXPOSE 80
WORKDIR /app

RUN apt-get update -qq && \
    apt-get install -y \
    git \
    vim \
    bash \
    gnupg \
    libicu-dev \
    libzip-dev \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    unzip \
    zip \
    libpq-dev \
    zlib1g-dev && \
    curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer && \
    apt-get clean && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

RUN docker-php-ext-configure zip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) intl opcache pdo pdo_mysql gd zip \
    && pecl install apcu \
    && docker-php-ext-enable apcu
COPY ./.docker/conf/php.ini /usr/local/etc/php/conf.d/app.ini

# Apache
COPY ./.docker/errors /errors
COPY ./.docker/conf/vhost.conf /etc/apache2/sites-available/000-default.conf
COPY ./.docker/conf/apache.conf /etc/apache2/conf-available/z-app.conf

# Install Symfony CLI
RUN curl -1sLf 'https://dl.cloudsmith.io/public/symfony/stable/setup.deb.sh' | bash && apt install -y symfony-cli

COPY . /app
RUN cd /app && composer install --no-dev --optimize-autoloader

RUN chmod -R 777 var/cache var/log

RUN a2enmod rewrite remoteip && \
    a2enconf z-app