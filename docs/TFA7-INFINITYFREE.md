# Upgrade the hosted TFA6 POS to TFA7

Finish and verify TFA7 locally first. GD must also be available in the hosting
PHP environment. Missing GD causes image processing to fail; do not bypass the
package requirement with --ignore-platform-reqs.

## Database upgrade

Back up the existing POS database with phpMyAdmin Export first. Select the SAME
POS database used by TFA6 and import this SQL once:

```text
database/sql/tfa7-avatar-upgrade.sql
```

It adds users.avatar as a nullable path field and records the new migration.
Customer and user rows are preserved. If the avatar column already exists, stop
and check whether this migration was applied before; do not run ALTER again.
Do not re-import tfa6-import.sql into a populated database.

For a NEW empty database, import tfa6-import.sql first and then the avatar upgrade.
The two supplied SQL scripts correspond to the migrations included in source.

## Deployment files

Prepare a fresh DEPLOYMENT COPY of the locally upgraded TFA7 source. In that copy:

```powershell
$env:Path = "C:\php;" + $env:Path
php artisan optimize:clear
composer install --no-dev --prefer-dist
```

The production vendor folder must include Intervention Image and its dependencies.
Uploading only the changed controller files without the new vendor dependencies
will fail. Keep the updated composer.lock. No npm build is required.

Use the already working HOSTED .env with its real APP_KEY and MySQL settings.
Keep APP_ENV=production, APP_DEBUG=false, SESSION_DRIVER=file, CACHE_STORE=file,
and QUEUE_CONNECTION=sync. Do not overwrite it with local SQLite settings.
APP_URL must match the actual website. Keep the root .htaccess and public/.htaccess
from TFA6. No avatar-specific rewrite rule and no storage:link are required.

Upload the upgraded app, config, routes, resources, public, and production vendor
contents into the correct domain htdocs. Keep .env and storage uploads intact.
Preserve storage/app/public/avatars: future prepared images are written there.
Do not delete the whole storage or bootstrap/cache folder during an upgrade.
If bootstrap/cache/config.php exists and contains old settings, remove that
single generated file. Keep required folders and normal permissions.

If uploading a complete deployment ZIP, exclude .env and runtime storage CONTENTS
when upgrading an already live site, so the existing server configuration and
avatars are retained. Include needed empty directories for a fresh host. Remove
the archive after extraction. Never upload .git, tests, local database files, or
private logs/caches into public. Confirm FTP/extraction reported no failed files.

## Verify

- Customer and user listings still show the existing records.
- Both Create buttons work; invalid values show errors and retain entered text.
- Edit pages are prefilled and submit to the correct record.
- A valid JPEG/PNG <= 2 MB is prepared and its thumbnail appears on /users.
- A user without an image shows the placeholder.
- Editing text without a new image retains the old one.
- Oversized/non-image files are rejected without changing the profile.
- /avatars/not-an-avatar.php returns 404; /.env and private logs are inaccessible.
- APP_DEBUG remains false; inspect storage/logs/laravel.log privately for errors.

If an upload is rejected before Laravel, check hosting upload/request limits.
If the new avatar service cannot be found, check the uploaded app files and
vendor dependencies. If the avatar column is missing, verify the SQL upgrade
ran in the same database configured in the hosted .env.

Commit and push the updated application source and composer.lock, then add the
hosted link to README-TFA7.md. GitHub commits do not update InfinityFree by themselves.
