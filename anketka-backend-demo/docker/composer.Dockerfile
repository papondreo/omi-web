# Та же версия PHP, что и в php-fpm.Dockerfile (менять вместе);
# composer и его entrypoint — из официального образа
FROM php:8.3.36-cli-alpine

RUN apk add --no-cache bash git unzip tini \
    && docker-php-ext-install pdo_mysql

COPY --from=composer:2.8 /usr/bin/composer /usr/bin/composer
COPY --from=composer:2.8 /docker-entrypoint.sh /docker-entrypoint.sh

ENV COMPOSER_ALLOW_SUPERUSER=1 \
    COMPOSER_HOME=/tmp
WORKDIR /app

ENTRYPOINT ["/docker-entrypoint.sh"]
CMD ["composer"]
