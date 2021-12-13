# Application ASSIA
Application d’Accompagnement Social et Solidaire Inter-Associative

## Version
3.6.2 13/12/2021

## Author
Romain MADELAINE

## Repository Git
https://github.com/RomMad/app-assia

## Demo version
https://demo.app-assia.org


## Requirements
- PHP 7.4 : https://windows.php.net/download/)
- Composer >=2.0 (https://getcomposer.org/)
- Apache >=2.4 (https://laragon.org/)
- Mysql >=5.7.24 or MariaDB >=10.3
- Symfony CLI (https://symfony.com/download/)
- Npm >=6.12 or Yarn >=1.22 (https://nodejs.org/en/download/)

## Installation
### 1. Clone the repository in a folder
```bash
git clone https://github.com/RomMad/app-assia.git <folder>
```

### 2. Create .env file
Go in the project directory and copy the ".env.local" to ".env":
```bash
copy .env.local .env
```

### 3. Install all PHP dependencies with Composer
```bash
composer install
```

### 4. Create database and make migration
```bash
php bin/console doctrine:database:create
php bin/console make:migration
php bin/console doctrine:migrations:migrate
```

### 5. Load data fixtures
Doctrine fixtures:
```bash
php bin/console doctrine:fixtures:load
```

### 6. Install all JS dependencies and build assets (SCSS, JS)
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

### 7. Clear cache
```bash
php bin/console cache:clear
```

### 8. Run server
```bash
symfony serve
```
If you don't have the Symfony binary installed, run `php -S localhost:8000 -t public/`

### 9. Create a new user or use a test user
You can create a new user with this command:
```bash
php bin/console app:user:create
```

Or you can also to log to the app with the test user:
- Login: user_test
- Password: test123

And go to "https://127.0.0.1:8000/"

## Tests
### 1. Init tests
```bash
php bin/console cache:clear -e prod; php bin/console cache:clear -e test; php bin/console d:d:drop -e test --force; php bin/console d:d:create -e test; php bin/console d:schema:update -e test --force; php bin/console hautelook:f:l -e test -n; 
```
### 2. Unit tests (entities and repositories)
```bash
php bin/phpunit tests/Entity; php bin/phpunit tests/Repository;
```
### 3. Functionnal tests (controllers)
```bash
php bin/phpunit tests/Controller;
```
Or
```bash
php bin/phpunit tests/Controller/App; php bin/phpunit tests/Controller/Admin; php bin/phpunit tests/Controller/Organization; php bin/phpunit tests/Controller/People; php bin/phpunit tests/Controller/Support; php bin/phpunit tests/Controller/Evaluation; php bin/phpunit tests/Controller/Note; php bin/phpunit tests/Controller/Rdv; php bin/phpunit tests/Controller/Document; php bin/phpunit tests/Controller/Payment; 
```
### 4. EndToEnd tests
```bash
php bin/phpunit tests/EndToEnd;
```