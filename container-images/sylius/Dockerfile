FROM docker.io/alpine:3.15

RUN apk --update add nginx nginx-mod-http-headers-more nginx-mod-http-brotli curl php7 php7-fpm php7-exif php7-json php7-pdo_mysql php7-phar php7-openssl php7-dom php7-simplexml php7-xml php7-tokenizer php7-pdo php7-fileinfo php7-intl php7-curl php7-gd php7-xmlwriter php7-session php7-opcache

COPY container-images/sylius/entrypoint.sh /bin/entrypoint.sh

RUN chmod +x /bin/entrypoint.sh

RUN adduser -h /app -u 10000 -D web root && \
    mkdir -p /tmp/nginx

WORKDIR /app
COPY vendor vendor
COPY bin bin
COPY src/Controller src/Controller
COPY src/Entity src/Entity
COPY src/EventListener src/EventListener
COPY src/Form src/Form
COPY src/Repository src/Repository
COPY src/Service src/Service
COPY src/Twig src/Twig
COPY src/Menu src/Menu
COPY src/Kernel.php src/Kernel.php
COPY src/Migrations src/Migrations
COPY container-images/sylius/php.ini /etc/php7/php.ini
RUN mkdir -p src/Migrations


COPY templates templates
COPY translations translations
COPY public public
COPY config config
COPY .env .env
COPY nginx-config/nginx.conf /etc/nginx/nginx.conf
COPY nginx-config/fastcgi_params.normal /etc/nginx/fastcgi_params.normal
COPY nginx-config/php-fpm-www.conf /etc/php7/php-fpm.d/www.conf
COPY .env.prod .env.prod

ENV APP_ENV=prod

EXPOSE 8080

HEALTHCHECK --interval=1m --timeout=30s --retries=3 CMD curl --fail http://localhost:8080 || exit 1

ENTRYPOINT ["/bin/entrypoint.sh"]
