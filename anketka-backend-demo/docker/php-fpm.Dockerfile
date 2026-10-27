# Патч-версия закреплена: та же, что на проде (менять вместе с composer.Dockerfile)
FROM php:8.3.36-fpm

RUN docker-php-ext-install pdo_mysql

COPY docker/php/zz-custom.ini /usr/local/etc/php/conf.d/zz-custom.ini
