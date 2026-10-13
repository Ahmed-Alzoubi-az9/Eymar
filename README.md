# Eymar Marketplace

Eymar is a Laravel 13 marketplace connecting customers with companies,
contractors, and skilled professionals. Customers can post projects and choose
from provider quotes, or shop provider-listed construction products. The
interface supports English and Arabic, including right-to-left layout.

## Features

- Public registration for customers and companies; customers can activate or
  remove a contractor/craftsman profile without losing their buyer account.
  Staff-created administrator accounts cannot be self-registered.
- Strong-password validation, verified email normalization, role-specific
  login destinations, and token-based password reset.
- Customer and company profiles with image uploads and validated contact data.
- Project requests, provider quotes, and customer quote acceptance.
- Company catalogues for materials, equipment, and machinery, including
  searchable listings, category, price, stock, experience, location, and
  availability filters.
- Product details for manufacturing, storage, safety, usage, and machinery
  drivers, with optional daily rental rates for equipment and machinery. New
  products are reviewed by staff before publication.
- Customer purchase and rental carts, seller-grouped checkout orders, purchase
  stock handling, rental date availability checks, and order status tracking.
  Checkout currently supports cash on delivery in JOD; no online payment
  provider is configured, and Eymar does not store payment-card details.
- Community posts, comments, likes, notifications, and staff account
  moderation.

## Requirements

- PHP 8.3 or later with the extensions required by Laravel
- Composer
- Node.js 20.19+ or 22.12+
- MySQL or MariaDB with the PHP `pdo_mysql` extension enabled

## Setup

From this directory, install dependencies, prepare the local environment, and
build the frontend:

```powershell
composer install
Copy-Item .env.example .env
php artisan key:generate
npm install
npm run build
```

The default local configuration uses XAMPP's MySQL service (`127.0.0.1:3306`,
database `eymar_marketplace`, user `root`, and no password). If your MySQL
account uses a password, set `DB_PASSWORD` in `.env`. Create the database
through phpMyAdmin or run migrations after confirming the configured database
user has permission to create it. Then seed the service and product categories
and enable public image URLs:

```powershell
php artisan migrate --seed
php artisan storage:link
```

Start the application:

```powershell
php artisan serve
```

Create a staff administrator from the command line when needed. The password is
prompted privately and must contain at least 12 characters:

```powershell
php artisan staff:create "Administrator" admin@example.com
```

For frontend development with hot reload, run `npm run dev` in a second
terminal while the Laravel server is running. Profile, company-logo, and
product images are stored on the public disk; the storage link command above
makes them accessible. Password reset emails use the configured Laravel mail
transport.

## Validation

Run the test suite and create a production frontend build:

```powershell
php artisan test
npm run build
```
