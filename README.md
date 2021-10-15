# Application ASSIA
Application d’Accompagnement Social et Solidaire Inter-Associative

## Version
3.1.9 15/10/2021

## Creator
Romain MADELAINE

## Repository Git
https://github.com/RomMad/app-assia

## Demo version
https://demo.app-assia.org


## Requirements
- PHP 7.4 (https://windows.php.net/download/)
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
symfony serve -d
```
### 9. Create a new user or use a test user
You can create a new user with this command:
```bash
php bin/console app:user:create
```

Or you can also to log to the app with the test user:
- Login: user_test
- Password: test123

And go to "https://127.0.0.1:8000/"