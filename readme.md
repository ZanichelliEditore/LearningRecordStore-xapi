[![Build Status](https://travis-ci.org/ZanichelliEditore/LearningRecordStore-xapi.svg?branch=travisCI)](https://travis-ci.org/ZanichelliEditore/LearningRecordStore-xapi)
[![codecov](https://codecov.io/gh/ZanichelliEditore/LearningRecordStore-xapi/branch/travisCI/graph/badge.svg)](https://codecov.io/gh/ZanichelliEditore/LearningRecordStore-xapi)


# About the project

An LRS implementing the xAPI to send data to AWS S3 storage.

# Starting

View Docker and Docker-compose documentations:

    - https://docs.docker.com

    - https://docs.docker.com/compose/

See the link above to know better xAPI structure:

    - https://github.com/adlnet/xAPI-Spec

# Command shortcut for Docker

Use `make` to execute commands based on `Makefile` list:

- `up`: Turn on container services
- `stop`: Turn off container services
- `down`: Turn off and remove container services
- `build`: Build container images
- `shell`: Open a shell con container app
- `composer_install`: Execute composer install
- `composer_update`: Execute composer update
- `run_tests`: Execute phpunit
- `run_tests_coverage`: Execute phpunit with coverage

# Requirements Setup

## Docker: Starting and stopping containers

Build the Docker images with `docker-compose -f docker-compose.dev.yml up --build -d`.

Once created, the containers can be **started** anytime with the following command:

    docker-compose -f docker-compose.dev.yml up -d

To **stop** the containers, use instead:

    docker-compose -f docker-compose.dev.yml stop

Enter in the container (with the command above) and run the next commands inside it:

    docker exec -it lrs_app bash

Then follow the step from **3 to 6** described after.

# Application Setup

These are the instructions to follow to set up the project on your local environment.

1.  Git clone the repository into your folder.

    https://github.com/ZanichelliEditore/LearningRecordStore-xapi.git

2.  Copy env.example to .env

3.  Install the required dependencies with composer

        composer install

4.  Generate a random application key

        php artisan key:generate

5.  Launch migration with seeder

        php artisan migrate --seed

6.  Install passport and create client credentials

        php artisan passport:install

## Launch application without using Docker

    php -S localhost:8000 public/index.php

# Accessing services

- **phpmyadmin**: http://localhost:8086

- **Documentation**: http://localhost:8085/api/documentation

# Run Test

- Run every method

  - `docker exec lrs_app vendor/bin/phpunit`

- To generate the HTML code coverage report pass the following option: `--coverage-html tmp/coverage`
  - `docker exec lrs_app vendor/bin/phpunit --coverage-html tmp/coverage`


# Authentication
The dafult authentication choosen in the project is **Basic Auth**.

There is the possibility to change it and switch to an **Oauth2** authentication (based on client credentials) thanks to passport.

### Launch Passport Test
If you decided to change authentication type then you have to change the tests that will be launched.

In *phpunit.xml* file, inside `testsuite` tag change the path exclude from *`./tests/xAPI/Passport`* to *`./tests/xAPI/BasicAuth`*.

When you add or modify tests classes launch **composer dump-autoload** inside the container.

Then change also the security schema defined in swagger.php and in the controllers.
