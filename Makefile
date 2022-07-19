SHELL := /bin/bash

tests:
	symfony check:security
	symfony console cache:clear --env=test
	symfony console doctrine:database:drop -e test --force
	symfony console doctrine:database:create -e test
	symfony console doctrine:schema:update -e test --force
	symfony php bin/phpunit tests/Entity
	symfony php bin/phpunit tests/Repository
	symfony php bin/phpunit tests/Command
	symfony php bin/phpunit tests/Controller
	symfony console cache:clear --env=test
	symfony console doctrine:database:drop -e test --force
	symfony console doctrine:database:create -e test
	symfony console doctrine:schema:update -e test --force
	symfony console hautelook:fixtures:load -e test -n
	symfony php bin/phpunit tests/EndToEnd
.PHONY: tests

tests-end-to-end:
	symfony console cache:clear --env=test
	symfony console doctrine:database:drop -e test --force
	symfony console doctrine:database:create -e test
	symfony console doctrine:schema:update -e test --force
	symfony console hautelook:fixtures:load -e test -n
	symfony php bin/phpunit tests/EndToEnd
.PHONY: tests-end-to-end

quality:
	symfony php ./vendor/bin/phpstan analyse -c phpstan.neon
.PHONY: quality