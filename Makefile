SHELL := /bin/bash

clear-cache-test:
	$(clear-cache-test)
.PHONY: clear-cache-test

tests:
	symfony check:security
	$(phpstan)
	$(php-cs-fixer-dry)
	$(clear-cache-test)
	symfony php bin/phpunit tests/Entity
	symfony php bin/phpunit tests/Repository
	symfony php bin/phpunit tests/Command
	symfony php bin/phpunit tests/Controller
	$(clear-cache-test)
	symfony php bin/phpunit tests/EndToEnd
.PHONY: tests

tests-end-to-end:
	$(clear-cache-test)
	symfony php bin/phpunit tests/EndToEnd
.PHONY: tests-end-to-end

quality:
	$(phpstan)
	$(php-cs-fixer)
	symfony console lint:yaml config --parse-tags
	symfony console lint:twig templates
.PHONY: quality

define clear-cache-test
	symfony console cache:clear --env=test
	symfony console doctrine:database:drop -e test --force
	symfony console doctrine:database:create -e test
	symfony console doctrine:schema:update -e test --force
	symfony console hautelook:fixtures:load -e test -n
endef

define phpstan
	symfony php ./vendor/bin/phpstan analyse -c phpstan.neon
endef

define php-cs-fixer
	symfony php ./tools/php-cs-fixer/vendor/bin/php-cs-fixer fix src --verbose
endef

define php-cs-fixer-dry
	symfony php ./tools/php-cs-fixer/vendor/bin/php-cs-fixer fix src --dry-run --verbose
endef