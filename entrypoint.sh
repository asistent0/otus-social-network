#!/bin/bash

composer install

PRIVATE_JWT_FILE="config/jwt/private.pem"
PUBLIC_JWT_FILE="config/jwt/public.pem"

# Проверка, если файл НЕ существует
if [ ! -f "$PRIVATE_JWT_FILE" ]; then
    bin/generate_jwt_keys.sh
    chmod 644 "$PRIVATE_JWT_FILE" "$PUBLIC_JWT_FILE"
fi

php bin/console doctrine:cache:clear-metadata
php bin/console doctrine:cache:clear-query
php bin/console doctrine:cache:clear-result
php bin/console doctrine:migrations:migrate --no-interaction
php bin/console cache:clear

chown -R www-data:www-data var

php-fpm
