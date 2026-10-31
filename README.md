# S4M Trust Bank

S4M Trust Bank is a simulated online banking management system built with Laravel. It is intended for local development and demonstration only; it is not connected to a real bank and must not be used for real financial activity.

## Overview

The application provides a customer-facing banking experience backed by PostgreSQL. Account activity is persisted through Laravel migrations and Eloquent models, and sensitive account operations use the application's existing validation, authorization, and transaction handling.

## Features

- Customer registration and authentication
- Bank account dashboard and balance management
- Deposit and withdrawal workflows
- Fund transfers and transfer receipts
- Transaction history and bank statement views
- Profile and notification management
- Security settings, login history, activity logs, recovery codes, and two-factor authentication support
- Email notifications for relevant account activity

## Technologies

- Laravel 13
- PHP 8.3 or newer (PHP 8.5 recommended for this project)
- PostgreSQL
- Blade templates
- Bootstrap-compatible styling and Tailwind CSS
- Vite

## Requirements

- PHP 8.3+
- Composer
- Node.js and npm
- PostgreSQL
- PHP `pdo_pgsql` and `pgsql` extensions

## Installation

From the project directory:

```bash
composer install
npm install
```

Copy the environment template and generate a local application key:

```bash
cp .env.example .env
php artisan key:generate
```

On Windows PowerShell, use `Copy-Item .env.example .env` instead of `cp`.

## Environment configuration

Edit `.env` and set values for the local environment. Never commit `.env` or copy real credentials into `.env.example`.

At minimum, configure:

- `APP_URL`
- `DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, and `DB_PASSWORD`
- `MAIL_USERNAME`, `MAIL_PASSWORD`, and `MAIL_FROM_ADDRESS` if email delivery is enabled

The application key must be generated with `php artisan key:generate`; do not share or commit it.

## PostgreSQL setup

Create a local PostgreSQL database and user, then set the matching values in `.env`. For example, using `psql` with an existing PostgreSQL administrator account:

```sql
CREATE USER s4m_trust_bank_user WITH PASSWORD 'choose-a-local-password';
CREATE DATABASE s4m_trust_bank OWNER s4m_trust_bank_user;
```

Use the created values for `DB_DATABASE`, `DB_USERNAME`, and `DB_PASSWORD`. The password above is only an example placeholder; choose a local value and keep it in `.env`.

Run the migrations:

```bash
php artisan migrate
```

If seed data is added to the database seeder in the future, run:

```bash
php artisan db:seed
```

## Running the application

Start the Laravel development server:

```bash
php artisan serve
```

Open `http://localhost:8000` in a browser.

For frontend asset development, run Vite in a separate terminal:

```bash
npm run dev
```

Build production frontend assets with:

```bash
npm run build
```

## Tests

Run the configured Laravel test suite with:

```bash
composer test
```

## GitHub repository

This working tree is prepared for the `smsudipto/Bank-Management-System-react` GitHub repository. Confirm the remote URL before pushing:

```bash
git remote -v
```

Do not commit, push, or publish until you have reviewed the staged files and confirmed that no environment files or credentials are included.
