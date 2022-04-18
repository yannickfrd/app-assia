# Application ASSIA

Application d’Accompagnement Social et Solidaire Inter-Associative

## Version

3.18.0 18/04/2022

## Author

Romain MADELAINE

## Repository Git

<https://github.com/RomMad/app-assia>

## Demo version

<https://demo.app-assia.org>

## Requirements

- PHP 7.4 (<https://windows.php.net/download/>)
- Composer >=2.0 (<https://getcomposer.org/>)
- Apache >=2.4 (<https://laragon.org/>)
- Mysql >=5.7.24 or MariaDB >=10.3
- Symfony CLI (<https://symfony.com/download/>)
- Npm >=6.12 or Yarn >=1.22 (<https://nodejs.org/en/download/>)

## Installation

### 1. Clone the repository in a folder

```bash
git clone https://github.com/RomMad/app-assia.git <folder>
```

### 2. Install all PHP dependencies with Composer

```bash
composer install
```

### 3. Create database and make migration

```bash
    symfony console doctrine:database:create
    symfony console make:migration
    symfony console doctrine:migrations:migrate
```

### 4. Load data fixtures

Doctrine fixtures:

```bash
symfony console doctrine:fixtures:load
```

### 5. Install all JS dependencies and build assets (SCSS, JS)

You can use Npm:

```bash
npm install
    npm run build
```

Or Yarn:

```bash
yarn install
    yarn run build
```

### 6. Clear cache

```bash
symfony console cache:clear
```

### 7. Run server

```bash
symfony serve
```

If you don't have the Symfony binary installed, run `php -S localhost:8000 -t public/`

### 8. Create a new user or use a test user

You can create a new user with this command:

```bash
symfony console app:user:create
```

Or you can also to log to the app with the test user:

- Login: john_doe
- Password: password

And go to "https://127.0.0.1:8000/"

## Tests

### 1. Init tests

```bash
symfony console cache:clear -e prod; 
symfony console cache:clear -e test; 
symfony console doctrine:database:drop -e test --force;
symfony console doctrine:database:create -e test;
symfony console doctrine:schema:update -e test --force;
symfony console hautelook:fixtures:load -e test -n;
```

### 2. Unit tests (entities and repositories)

```bash
symfony php bin/phpunit tests/Entity; 
symfony php bin/phpunit tests/Repository;
```

### 3. Functionnal tests (controllers)

```bash
symfony php bin/phpunit tests/Controller;
```

Or

```bash
symfony php bin/phpunit tests/Controller/App; 
symfony php bin/phpunit tests/Controller/Admin; 
symfony php bin/phpunit tests/Controller/Organization; 
symfony php bin/phpunit tests/Controller/People; 
symfony php bin/phpunit tests/Controller/Support; 
symfony php bin/phpunit tests/Controller/Evaluation; 
symfony php bin/phpunit tests/Controller/Note; 
symfony php bin/phpunit tests/Controller/Rdv; 
symfony php bin/phpunit tests/Controller/Event; 
symfony php bin/phpunit tests/Controller/Document; 
symfony php bin/phpunit tests/Controller/Payment; 
```

### 4. EndToEnd tests

```bash
symfony php bin/phpunit tests/EndToEnd;
```

### 5. All tests

You can use this script with Composer to execute all tests:

```bash
composer tests-dev
```

### PHPStan

Analyze:

```bash
vendor/bin/phpstan analyse -c phpstan.neon
```

### PHP CS Fixer

Analyze:

```bash
tools/php-cs-fixer/vendor/bin/php-cs-fixer fix src --dry-run --verbose
```

Fix:

```bash
tools/php-cs-fixer/vendor/bin/php-cs-fixer fix src --verbose
```

### Prettier (JS/Twig)

Analyze:

```bash
yarn prettier assets --check --config prettierrc
```

Fix:

```bash
yarn prettier assets --write --config prettierrc
```
