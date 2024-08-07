# Alpine 3.15 required to get php7 package required for this Sylius setup
FROM docker.io/alpine:3.15

RUN apk --update add curl git php7 php7-exif php7-json php7-phar php7-openssl php7-dom php7-simplexml php7-xml php7-tokenizer php7-pdo php7-fileinfo php7-intl php7-curl php7-gd php7-xmlwriter php7-session

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin --filename=composer

ENV COMPOSER_HOME="/src/composer-home"
ENV COMPOSER_MEMORY_LIMIT=-1

RUN adduser -h /home/inventiv -u 1000 -D inventiv root
RUN mkdir -p $COMPOSER_HOME && chown -R 1000:root /src
USER inventiv

WORKDIR /src
