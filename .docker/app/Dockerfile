ARG COMPOSER_VERSION="2.8.4"

FROM ghcr.io/roadrunner-server/velox:2025.1.1 as velox

FROM golang:1.25-alpine as rr

ARG APP_VERSION="2025.1.1"
ARG GH_TOKEN

# copy required files from builder image
COPY --from=velox /usr/bin/vx /usr/bin/vx
COPY velox.toml .

# we don't need CGO
ENV CGO_ENABLED=0
ENV GH_TOKEN=${GH_TOKEN}
ARG CACHE_BUST=21

RUN echo $CACHE_BUST

# RUN build
RUN vx build -c velox.toml -o /usr/bin/

FROM composer:${COMPOSER_VERSION} AS composer

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

RUN chmod +x .docker/app/entrypoint.sh

RUN composer config --no-plugins allow-plugins.spiral/composer-publish-plugin false
RUN composer install --no-dev --optimize-autoloader

CMD [".docker/app/entrypoint.sh"]