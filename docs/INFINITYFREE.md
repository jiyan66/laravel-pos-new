# Host TFA6 on InfinityFree

Complete local setup and verification first. Use a separate hosting account or
subdomain as allowed by your dashboard; preserve your existing Tasks application.

## Database

1. Create a NEW empty MySQL database in the hosting account.
2. Open phpMyAdmin for that database and import database/sql/tfa6-import.sql ONCE.
3. Confirm customer_count = 5 and user_count = 5.
4. Note the exact hostname, full database name, username, and password shown by
   InfinityFree. The database hostname is not localhost.

## Deployment copy

Copy the working project to a sibling folder, laravel-pos-tfa6-deploy. Open that
copy and run:

```powershell
$env:Path = "C:\php;" + $env:Path
php artisan optimize:clear
composer install --no-dev --prefer-dist
```

This installs production dependencies locally because the host has no terminal.
Keep composer.lock. No npm build is required. Do not run tests in this production
copy because PHPUnit is intentionally omitted.

## Hosted environment

Edit the deployment copy's existing .env. Keep the actual APP_KEY from local
setup; do not replace the whole file with unfilled placeholders. Add or edit
these settings without duplicate entries:

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_URL=https://YOUR-POS-SUBDOMAIN
APP_TIMEZONE=Asia/Manila
DB_CONNECTION=mysql
DB_HOST=YOUR_HOSTING_MYSQL_HOSTNAME
DB_PORT=3306
DB_DATABASE=YOUR_FULL_DATABASE_NAME
DB_USERNAME=YOUR_MYSQL_USERNAME
DB_PASSWORD="YOUR_MYSQL_PASSWORD"
SESSION_DRIVER=file
CACHE_STORE=file
QUEUE_CONNECTION=sync
SESSION_SECURE_COOKIE=true
```

A template is included at deployment/infinityfree.env.example. Use the database
credentials supplied for your hosting account. Keep .env private and out of Git.
If initially using HTTP before SSL is active, use an http APP_URL and
SESSION_SECURE_COOKIE=false; switch both after verifying HTTPS.

Copy deployment/htdocs.htaccess to the deployment root and rename the copy to
.htaccess. Its contents are:

```apache
RewriteEngine On
RewriteRule (.*) /public/$1 [L]
```

Keep public/.htaccess too. Do not move application code into public.

## Upload

Select the following CONTENTS of the deployment copy and upload/extract them
DIRECTLY inside your domain's htdocs:

```text
app
bootstrap
config
public
resources
routes
storage
vendor
.env
.htaccess
artisan
composer.json
composer.lock
```

The resulting paths must include htdocs/public/index.php,
htdocs/vendor/autoload.php, htdocs/.env, and htdocs/.htaccess. Do not add an outer
laravel-pos-tfa6-deploy folder inside htdocs.

Do not upload .git, tests, documentation, scripts, local database files, or local
logs, cached views, and sessions. Preserve bootstrap/cache,
storage/framework/cache/data, storage/framework/views,
storage/framework/sessions, storage/logs, and storage/app/public directories.

If using ZIP upload and extraction, include dotfiles, then delete the uploaded
ZIP immediately after extraction because it contains .env. If uploads fail,
use the account's provided FTP settings in an FTP client and check failed files.
Composer autoloader optimization is disabled in this project's composer.json to
reduce generated file sizes; inspect any silently missing files after upload.

## Final checks

- /, /about, /customers, and /users all open.
- Both lists show five seeded records and CSS loads.
- /.env, /composer.json, and /storage/logs/laravel.log do not reveal their contents.
- APP_DEBUG remains false. Read storage/logs/laravel.log privately for errors.
- A stale bootstrap/cache/config.php can be deleted if present; keep its folder.
- If an error mentions SQLite on hosting, check the actual htdocs/.env for
  DB_CONNECTION=mysql and correct MySQL settings. Do not upload SQLite as a fix.
- Add the real live URL to your source README and submit it with the GitHub link.

Do not import the SQL again after a successful import into the same database.
Do not expose Artisan/SQL administration endpoints to run migrations remotely.

Hosting reference:
https://forum.infinityfree.com/t/how-to-install-a-laravel-site-on-infinityfree/118578
