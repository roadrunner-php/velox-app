ARG ROAD_RUNNER_IMAGE=2025.1
ARG COMPOSER_VERSION="2.8.4"

FROM composer:${COMPOSER_VERSION} AS composer

FROM ghcr.io/roadrunner-server/roadrunner:$ROAD_RUNNER_IMAGE AS rr
FROM ghcr.io/spiral/php-grpc:8.3

ARG APP_VERSION=v1.0
ENV COMPOSER_ALLOW_SUPERUSER=1

RUN set -eux; \
    apk -U upgrade -a \
    && \
    curl -sSLf \
        -o /usr/local/bin/install-php-extensions \
        https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions \
    && chmod +x /usr/local/bin/install-php-extensions \
    && install-php-extensions \
        pdo_pgsql \
    && install-php-extensions --cleanup

COPY --from=composer /usr/bin/composer /usr/bin/composer
COPY . /app
COPY --from=rr /usr/bin/rr /usr/bin/rr

WORKDIR /app

RUN venodr/bin/dload

RUN chmod +x .docker/entrypoint.sh

RUN composer config --no-plugins allow-plugins.spiral/composer-publish-plugin false
RUN composer install --no-dev --optimize-autoloader

CMD [".docker/entrypoint.sh"]