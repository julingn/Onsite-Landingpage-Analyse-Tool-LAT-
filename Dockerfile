FROM php:8.3-cli-alpine

RUN apk add --no-cache curl-dev libcurl openssl-dev \
    && docker-php-ext-install curl \
    && docker-php-ext-enable openssl || true

WORKDIR /app
COPY . .

EXPOSE 8080

CMD ["sh", "-c", "php -S 0.0.0.0:${PORT:-8080} -t ."]
