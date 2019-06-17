.PHONY: help up down shell npm_watch composer_update

ENV ?= dev
PROJECT ?= lrs

help:                             ## Show this help.
	@fgrep -h "##" $(MAKEFILE_LIST) | fgrep -v fgrep | sed -e 's/\\$$//' | sed -e 's/##//'
up:                               ## Turn on container services
	docker-compose --file docker-compose.$(ENV).yml up -d
stop:                             ## Turn off container services
	docker-compose --file docker-compose.$(ENV).yml stop
down:                             ## Turn off and remove container services
	docker-compose --file docker-compose.$(ENV).yml down
build:                            ## Build container images
	docker-compose --file docker-compose.$(ENV).yml build
rebuild:                            ## Build container images and activate
	docker-compose --file docker-compose.$(ENV).yml up -d --build
shell:                            ## Open a shell con container app
	docker exec -it $(PROJECT)_app bash
composer_install:                  ## Execute composer install
	docker exec $(PROJECT)_app composer install
composer_update:                  ## Execute composer update
	docker exec $(PROJECT)_app composer update
run_tests:                        ## Execute phpunit
	docker exec $(PROJECT)_app vendor/bin/phpunit
run_tests_coverage:                        ## Execute phpunit
	docker exec $(PROJECT)_app vendor/bin/phpunit --coverage-html tmp/coverage

.DEFAULT_GOAL := help
