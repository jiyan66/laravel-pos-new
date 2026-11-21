# Upgrade the existing InfinityFree TFA7 POS to TFA8

Finish the local installer and verification first. Retain a private export of
the EXISTING hosted MySQL database and a copy of the hosted files before uploading.
Use the same domain/root layout that already works for TFA7. TFA8 now requires
a local npm build; the earlier TFA6/TFA7 "no npm build" note no longer applies.

## 1. Add authentication fields to the existing hosted database

In phpMyAdmin select the SAME database used by the hosted POS and import ONCE:

`database/sql/tfa8-auth-upgrade.sql`

It adds email/password/verification/remember-token columns, a unique email index,
the password reset token table and the migration record. It preserves users,
customers and avatar paths. Existing users remain without login credentials.
Do not re-import TFA6's base SQL or TFA7's already-applied avatar SQL. Do not use
migrate:fresh. If the new fields or migration already exist, inspect the state
instead of running this ALTER a second time. Use either migrations OR this SQL
for a given database, not both. Fresh databases need TFA6 then TFA7 then TFA8 SQL.

## 2. Prepare a separate deployment copy

Use a copy of the COMPLETED LOCAL PROJECT, including the actual Breeze-generated
files and your generated lock files. The update archive alone is not deployable.
In that deployment copy run:

```powershell
$env:Path = "C:\php;" + $env:Path
$env:PHP_INI_SCAN_DIR = "C:\php\conf.d"
npm.cmd ci
npm.cmd run build
php artisan optimize:clear
composer install --no-dev --prefer-dist
```

Confirm `public/build/manifest.json` and its asset files exist. Do not run
Breeze's installer in the deployment copy or on the host. Breeze is a scaffolding
dependency: its published source works with production dependencies after
`--no-dev`. Keep Intervention Image and GD available for TFA7 avatars.

## 3. Upload the updated application

Keep the host's existing private `.env`, APP_KEY, MySQL connection and runtime
storage. APP_ENV=production and APP_DEBUG=false. APP_URL must match the site;
use SESSION_DRIVER=file, CACHE_STORE=file and QUEUE_CONNECTION=sync as before.
On working HTTPS, use SESSION_SECURE_COOKIE=true. Do not copy a local SQLite
`.env` onto the server. No database password belongs in Git or a public file.

Upload the CONTENTS of these completed project directories to the matching
locations under the already working domain htdocs:

- `app`, including actual Breeze Auth controllers, LoginRequest and View components
- `config`, including auth/hashing/mail
- `routes`, including BOTH web.php and Breeze's generated auth.php
- `resources`, including generated components/layouts/auth views and POS overrides
- `public/css`, `public/images`, and the complete generated `public/build`
- production `vendor` (updated dependencies/autoloader), composer.json and composer.lock

Preserve root `.htaccess` and `public/.htaccess`. Keep `public/index.php` in its
working location. Remove a stale `public/hot` if present, so Vite uses the build
manifest. Do not upload .git, node_modules, test suites, private backups, local
SQLite files or local logs/sessions. Do not overwrite existing storage avatars.
The app serves avatars through its route; storage:link is not needed.

If a cached `bootstrap/cache/config.php` exists, remove that generated file only.
Also remove stale generated `bootstrap/cache/routes-*.php` files if present.
Do not delete the cache directories, .gitignore files or all of storage. For
compiled views, remove generated .php files from storage/framework/views only.
No public PHP "run artisan"/migration endpoint is needed or supplied.

An upload ZIP should contain only the selected deployment contents, not an
outer project folder. Exclude `.env` and runtime storage when upgrading the live
site. Remove the uploaded ZIP after extraction and check for failed uploads.

## 4. Test the hosted site

Logged out, `/customers` and `/users` must redirect to `/login`. Register a NEW
username/email because legacy seeded accounts have no shared password. Verify
both listings and forms after login; assign legacy credentials through Edit
User as needed. Test logout, invalid forms and a replacement avatar. Verify
login styling loads; a Vite manifest error means public/build was not uploaded
completely. Update the source README with the real hosted URL after it works.

Mail is set to log unless configured otherwise. Login/registration do not need
mail delivery. Enable a working SMTP transport before relying on reset emails.
Do not publish private logs containing reset links.

## Commit the complete upgraded source

In the original development project, first review `git status` and `git diff`.
Check that no .env, database, uploads or backup files are staged. Then commit the
source and both lock files to your existing repository and push normally.
The sibling `.before-tfa8-*` backup is private and should stay outside the repo.
GitHub changes do not automatically update InfinityFree.
