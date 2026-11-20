# IT0049 TFA6 Laravel POS

Database-backed customer and user account listings by Joseph Gian Carlo Mistica.
This is the complete TFA6 source project. Complete and verify this version before
applying the separate TFA7 update package.

## What is included

| Page | URL | Data source |
| --- | --- | --- |
| Home | / | Static Blade page |
| About | /about | Static developer information |
| Customer Accounts | /customers | Customer Eloquent model |
| User Accounts | /users | User Eloquent model |

Migrations create the schema. Seeders insert five realistic sample customers and
five users. The controllers query Eloquent; Blade loops over database objects.
Seed arrays are only the input used to populate the database.

The project follows the fields explicitly named in the TFA6 brief: customers
have full_name, email, and phone; users have username and full_name. IDs and both
Eloquent timestamps are included. Username is unique, and phone is nullable.
String lengths are implementation choices because the complete referenced TFA2
schema was not attached. User email and role fields from earlier static examples
are not assumed to exist. There is no login, password, or deletion workflow in
this activity.

## Requirements

- PHP 8.3+ (the existing C:\php PHP 8.4 installation is suitable).
- Composer 2 and Git.
- Standard Laravel PHP extensions, including PDO, cURL, Mbstring, Fileinfo,
  OpenSSL, DOM/XML, Ctype, Tokenizer, Session, Filter, Hash, and PCRE.
- pdo_sqlite/sqlite3 for the default local SQLite database.
- pdo_mysql for MySQL hosting. ZIP support helps Composer downloads.

No frontend build or Node.js installation is needed. CSS is already a public file.

## Windows setup

Extract so `artisan` is located at:

```text
C:\xampp\htdocs\laravel-pos-tfa6\artisan
```

Do not paste the project inside your existing POS or Tasks project. Open this
new folder in VS Code and run:

```powershell
$env:Path = "C:\php;" + $env:Path
powershell -NoProfile -ExecutionPolicy Bypass -File .\SETUP.ps1
powershell -NoProfile -ExecutionPolicy Bypass -File .\VERIFY.ps1
php artisan serve
```

Review the scripts before execution. ExecutionPolicy Bypass applies only to
those script processes; it does not change your permanent policy. SETUP installs
dependencies, creates .env and a SQLite file if missing, generates a missing key,
and runs migrations and seeders. It does not run migrate:fresh or delete records.

Open http://127.0.0.1:8000. If another Laravel server uses that port, stop it or
run `php artisan serve --port=8001` and open http://127.0.0.1:8001 instead.

## Manual setup alternative

In the project root:

```powershell
$env:Path = "C:\php;" + $env:Path
Copy-Item .env.example .env
New-Item -ItemType File database\database.sqlite
composer install --prefer-dist
php artisan key:generate
php artisan optimize:clear
php artisan migrate --seed
composer test
php artisan serve
```

Copy .env.example and create the database file only when they do not already
exist. Do not overwrite a configured .env. Composer creates composer.lock on
first install; commit that generated lock file to your repository.

## Using local MySQL instead

Create a NEW empty database named laravel_pos_tfa6 in XAMPP/phpMyAdmin. Start
XAMPP's MySQL service. Edit these existing entries in .env, adding missing ones:

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel_pos_tfa6
DB_USERNAME=root
DB_PASSWORD=
```

Use your actual local credentials if different. Run:

```powershell
php artisan config:clear
php artisan migrate --seed
```

Do not use your Tasks application's database or an existing unrelated POS
schema. Re-seeding updates the known demo records without adding duplicates;
it does not clear all users or customers.

## Verification

VERIFY.ps1 runs Composer manifest validation, PHP lint, Blade view compilation,
and the eight baseline feature tests. Tests use an isolated in-memory SQLite
database, not your local/hosted database.

The tests cover five records in each table, repeat seeding, all four pages,
retrieval of newly inserted records, empty lists, escaped output, and unique
usernames. The included GitHub Actions workflow runs the suite after you push.
No CI result is claimed until your own repository runs it.

PHP and Composer are not installed in the package-authoring environment. File,
route, seed-data, and configuration consistency were checked there; PHP lint,
Blade compilation, and PHPUnit execution must be performed on a PHP-equipped
machine. Do not interpret the presence of tests as a claim that they passed.

Manual checks: all four navigation links work, both listings contain at least
five records, the About page identifies the developer, and the layout remains
usable on a narrow screen.

## Source layout

| Location | Purpose |
| --- | --- |
| app/Models | Customer and User Eloquent models |
| app/Http/Controllers | Page selection and database retrieval |
| database/migrations | Customers/users table definitions |
| database/seeders | Five customers and five users |
| resources/views | Shared layout and four Blade views |
| public/css/app.css | Responsive styles |
| database/sql/tfa6-import.sql | MySQL schema and sample data for hosting |
| docs/INFINITYFREE.md | Hosting instructions |

## GitHub and submission

Create an empty GitHub repository, then run:

```powershell
 git init
 git add .
 git status
 git commit -m "Complete TFA6 Laravel POS with Eloquent"
 git branch -M main
 git remote add origin https://github.com/YOUR_USERNAME/laravel-pos-tfa6.git
 git push -u origin main
```

Replace the URL with your repository URL. Before committing, confirm .env,
vendor, local SQLite files, logs, and compiled caches are excluded. Include the
locally generated composer.lock, migrations, seeders, and SQL import.

Add your actual repository URL and live website URL below before submission:

- Repository: add your real URL.
- Hosted application: add your real URL.

For TFA7, preserve this folder/version and apply the update package to a copy.
No TFA7 forms or avatar code is included in this TFA6 baseline.

## References

- https://laravel.com/docs/13.x/migrations
- https://laravel.com/docs/13.x/seeding
- https://laravel.com/docs/13.x/eloquent
- https://laravel.com/docs/13.x/blade
