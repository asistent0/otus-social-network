FROM php:8.4-fpm AS php-base

RUN apt-get update && apt-get install -y \
    unzip \
    libzip-dev \
    libpq-dev \
    libicu-dev \
    libonig-dev \
    libxml2-dev \
    wget \
    librabbitmq-dev \
    openssl \
    cron \
    && docker-php-ext-install \
    zip \
    intl \
    pgsql \
    pdo_pgsql \
    mbstring \
    opcache \
    xml  \
    && pecl install amqp redis \
    && docker-php-ext-enable amqp redis

RUN mkdir -p /var/run/cron && chmod 755 /var/run/cron

RUN curl -sS https://getcomposer.org/installer | php && mv composer.phar /usr/local/bin/composer

ARG SOURCE_PATH=/var/www
WORKDIR $SOURCE_PATH
COPY . .

### Php backend
FROM php-base AS backend
ARG SOURCE_PATH=/var/www
WORKDIR $SOURCE_PATH

EXPOSE 9000

ENTRYPOINT ["sh", "./entrypoint.sh"]

### Supervisord
FROM php-base AS supervisord

RUN apt-get install -y supervisor && mkdir -p /var/log/supervisor

RUN mkdir -p /var/run && chmod 777 /var/run

COPY ./docker/supervisor/ /etc/supervisor/conf.d/
CMD ["/usr/bin/supervisord", "-n", "-c", "/etc/supervisor/supervisord.conf"]
