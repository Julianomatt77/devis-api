# Devis-api

- PHP 8.3
- Symfony 7.1.5 -> upgraded to 7.4 on 09/12/2025

## Installation

```bash
composer install
```

## Configuration

### Database

- MySQL 8 database

### Authentification

- add jwt folder with public and private key

### Mailer

- add MAILER_DSN env variable

## Run

```bash
symfony console doctrine:database:create
symfony console doctrine:migrations:migrate
symfony console doctrine:fixtures:load
symfony console cache:clear
symfony console cache:warmup
```

```bash
symfony serve
```
