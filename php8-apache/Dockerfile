FROM php:apache

WORKDIR /var/www/html

ARG WWWGROUP

ENV DEBIAN_FRONTEND noninteractive
ENV TZ=UTC

RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

RUN apt-get update && apt-get install -y \
        libpng-dev \
        zlib1g-dev \
        libxml2-dev \
        libzip-dev \
        libonig-dev \
        zip \
        curl \
        unzip \
        libmagickwand-dev --no-install-recommends \
        supervisor \
        ghostscript \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libpng-dev \
        libc-client-dev libkrb5-dev \
        gnupg gosu sqlite3 libcap2-bin python3 \
        libicu-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd \
    && docker-php-ext-install pdo_mysql \
    && docker-php-ext-install mysqli \
    && docker-php-ext-install zip \
    && docker-php-ext-install mbstring \
    && docker-php-ext-configure intl \
    && docker-php-ext-configure imap --with-kerberos --with-imap-ssl \
    && docker-php-source delete

RUN curl -sL https://deb.nodesource.com/setup_18.x | bash - \
    && apt-get install -y nodejs \
    && curl -sS https://dl.yarnpkg.com/debian/pubkey.gpg | apt-key add - \
    && echo "deb https://dl.yarnpkg.com/debian/ stable main" > /etc/apt/sources.list.d/yarn.list \
    && apt-get update \
    && apt-get install -y yarn \
    && apt-get -y autoremove \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

RUN sed -i '/disable ghostscript format types/,+6d' /etc/ImageMagick-6/policy.xml

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

COPY vhost.conf /etc/apache2/sites-available/000-default.conf

RUN pecl install -o -f redis \
&&  rm -rf /tmp/pear \
&&  docker-php-ext-enable redis

# Sqlserver
# RUN apt-get update \
#  && apt-get install --yes --no-install-recommends \
#         apt-transport-https \
#         unixodbc-dev \
#  && curl https://packages.microsoft.com/keys/microsoft.asc | apt-key add - \
#  && curl https://packages.microsoft.com/config/ubuntu/20.04/prod.list > /etc/apt/sources.list.d/mssql-release.list \
#  && apt-get update \
#  && ACCEPT_EULA=Y apt-get install --yes --no-install-recommends msodbcsql17 \
#  && ACCEPT_EULA=Y apt-get install -y mssql-tools \
#  && pecl install sqlsrv pdo_sqlsrv \
#  && apt-get clean \
#  && rm -rf /var/lib/apt/lists/*
# RUN docker-php-ext-enable sqlsrv pdo_sqlsrv


ENV PATH="/opt/mssql-tools/bin:${PATH}"

RUN a2enmod rewrite \
    && a2enmod headers


COPY start-container /usr/local/bin/start-container

COPY supervisord.conf /etc/supervisor/conf.d/supervisord.conf

COPY php.ini /usr/local/etc/php/php.ini

RUN chmod +x /usr/local/bin/start-container

ENTRYPOINT ["start-container"]