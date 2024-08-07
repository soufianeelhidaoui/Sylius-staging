#!/bin/sh

set -o errexit

env >> ./.env.$APP_ENV

php -d memory_limit=-1 bin/console cache:clear
php -d memory_limit=-1 bin/console doctrine:migrations:migrate --no-interaction
php -d memory_limit=-1 bin/console assets:install --symlink --relative public

php -d memory_limit=-1 bin/console fos:elastica:populate

php-fpm7

nginx -g 'daemon off;'
