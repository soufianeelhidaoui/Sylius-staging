<p align="center">
    <a href="https://sylius.com" target="_blank">
        <img src="https://demo.sylius.com/assets/shop/img/logo.png" />
    </a>
</p>

<h1 align="center">SA VGF</h1>

About
-----

Sylius is the first decoupled eCommerce platform based on [**Symfony**](http://symfony.com) and [**Doctrine**](http://doctrine-project.org).
The highest quality of code, strong testing culture, built-in Agile (BDD) workflow and exceptional flexibility make it the best solution for application tailored to your business requirements.
Enjoy being an eCommerce Developer again!

Powerful REST API allows for easy integrations and creating unique customer experience on any device.

We're using full-stack Behavior-Driven-Development, with [phpspec](http://phpspec.net) and [Behat](http://behat.org)

Documentation
-------------

Documentation is available at [docs.sylius.com](http://docs.sylius.com).

Installation
-------------

copy `.env.local.sample` to `env.local` and edit its content

### Install local Elastic Search with Docker

instantiate docker (see docker config file in `docker-compose.yml`)

Launch docker :
```
docker compose up
```

In `env.local` configure the correct password for Elastic Search :
```
ELASTICSEARCH_HOST=127.0.0.1
ELASTICSEARCH_PORT=9200
ELASTICSEARCH_USERNAME=elastic
ELASTICSEARCH_PASSWORD=okok
```

Troubleshooting
---------------

If something goes wrong, errors & exceptions are logged at the application level:

```bash
$ tail -f var/log/prod.log
$ tail -f var/log/dev.log
```

If you are using the supplied Vagrant development environment, please see the related [Troubleshooting guide](etc/vagrant/README.md#Troubleshooting) for more information.


Commands
------------

Populate ElasticSearch

```bash
$ bin/console fos:elastica:populate
```

Compile css
```bash
# Back sylius
$ yarn install
$ yarn build
# front
$ cd assets/shop
$ npm install
$ npm run build-theme
```

Run after Git pull

```bash
$ composer update -o
$ bin/console doctrine:migrations:migrate --no-interaction
$ bin/console assets:install --symlink --relative public
```


Watch & Compile css
```bash
$ cd assets/shop
$ npm run watch
```

Make migration

``php bin/console make:migration``

Run migration

``php bin/console doctrine:migrations:migrate``

# Platform.sh

**Connect to Platform.sh**

Previously you must install locally the [Platform.sh CLI](https://docs.platform.sh/administration/cli.html)

Go check the CLI platform.sh command in your account to get the **right projet** and the **right branch**.

```
platform get xxxxxxx -e develop
```

**Create tunnel to MySQL DB**

```
platform tunnel:single
```
Copy the database link url.


Connect with your local Mysql Client with the url returns 

Example :
```
mysql://user:@127.0.0.1:30000/main
```
