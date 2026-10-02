# Amader Hisab

Amader Hisab is a household expense tracker built with Laravel. It lets signed-in users record expenses, see spending summaries, and compare monthly household spending.

## Features

- Dashboard totals for all expenses, today, this week, and a selected month
- Daily average and spending breakdowns by category and payer
- Expense creation, editing, deletion, search, and filters for category, payer, and date
- Monthly reports with category comparisons and daily totals
- Login, session-based authentication, and throttled login attempts
- Seeded expense categories and local demo accounts

Each expense belongs to the signed-in user. Amount returned is subtracted from the amount paid to calculate the net expense.

## Requirements

- PHP 8.3 or newer
- Composer
- Node.js and npm
- SQLite with PHP's PDO SQLite extension, or another database supported by Laravel

## Local setup

1. Install PHP and JavaScript dependencies:

   ```sh
   composer install
   npm install
   ```

2. Create your environment file and application key:

   ```sh
   cp .env.example .env
   php artisan key:generate
   ```

   On Windows PowerShell, use `Copy-Item .env.example .env` instead of `cp`.

3. The default database connection is SQLite. Create its file if it does not exist:

   ```sh
   touch database/database.sqlite
   ```

   In Windows PowerShell:

   ```powershell
   New-Item database/database.sqlite -ItemType File
   ```

   To use a different database, update the `DB_*` settings in `.env` first.

4. Create the database tables and seed categories and demo users:

   ```sh
   php artisan migrate --seed
   ```

5. Build the frontend assets:

   ```sh
   npm run build
   ```

6. Start the Laravel development server:

   ```sh
   php artisan serve
   ```

   Open <http://127.0.0.1:8000>. For live frontend updates during development, run `npm run dev` in a second terminal instead of building assets each time.

## Local demo accounts

The database seeder creates these accounts, both with the password `password`:

| Email | Name |
| --- | --- |
| `superadmin@amaderhisab.com` | Super Admin |
| `biplob@amaderhisab.com` | Md Biplob Mia |

These credentials are for local development. Set secure credentials before deploying or exposing an environment outside your machine.

## Useful commands

```sh
php artisan migrate --seed  # Migrate the database and seed categories and demo users
php artisan test            # Run the PHPUnit test suite
vendor/bin/pint             # Format PHP code
npm run dev                 # Run Vite in development mode
npm run build               # Build production frontend assets
```

## Main routes

| Path | Description | Access |
| --- | --- | --- |
| `/login` | Sign in | Guests |
| `/dashboard` | Household summary and month selector | Signed-in users |
| `/expenses` | Search and manage expenses | Signed-in users |
| `/expenses/create` | Add an expense | Signed-in users |
| `/reports` | Monthly category and daily reports | Signed-in users |

