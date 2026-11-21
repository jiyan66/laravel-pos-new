# Mistica POS — TFA8

Laravel 13 POS account management, continued from TFA5–TFA7. Customer and user
controllers use all seven resource actions. Laravel Breeze provides login,
registration, logout and password handling. Authentication is required for
customer/user management and avatar delivery; Home and About remain public.

## Links for submission

- GitHub repository: **add the URL of this repository**
- Hosted application: **add the actual working HTTPS URL after deployment**

These are submission fields, not working links. Complete them before submitting.

## Upgrade the working TFA7 project

Use the separate TFA8 update package's `START-HERE.md` and `APPLY-TFA8.ps1`.
The installer runs the real Breeze scaffolder before applying the POS changes.
Do not run Breeze's installer again in an already upgraded checkout: it would
replace the customized registration, routes, views and tests.

## Run a fresh checkout of the completed TFA8 repository

Requires PHP 8.3+ (the existing PHP 8.4 setup is suitable), Composer, GD,
mbstring, fileinfo, OpenSSL, PDO and the selected database driver. The supplied
tests also need PDO SQLite, DOM and XML. Use Node.js LTS 22.13+ or 24+ and npm
for building the frontend. Do not upload `node_modules` to the host.

```powershell
$env:Path = "C:\php;" + $env:Path
# Only if this is where you enabled GD:
$env:PHP_INI_SCAN_DIR = "C:\php\conf.d"
composer install
Copy-Item .env.example .env
php artisan key:generate
New-Item -ItemType File -Path database/database.sqlite -Force
php artisan migrate --seed
npm.cmd ci
npm.cmd run build
powershell -NoProfile -ExecutionPolicy Bypass -File .\VERIFY.ps1
php artisan serve
```

The `.env` copy/key creation and empty SQLite creation above are for a FRESH
checkout only. Keep the original `.env`, key and database during an upgrade.
Use MySQL settings instead of SQLite when that is your selected database.
A completed source checkout must include `composer.lock`, `package-lock.json`,
the generated Breeze source and `docs/BREEZE-INSTALLATION.json`.

Open http://127.0.0.1:8000/register. Register a **new** username and email,
then use email/password on the login form. The five old seeded usernames are
preserved and have no default password. Once signed in, edit an older user to
assign its unique login email and initial password. Existing images remain.

## Behavior and scope

- `Route::resource('customers', CustomerController::class)` and the equivalent
  users registration are inside the `auth` group. Each controller implements
  index/create/store/show/edit/update/destroy.
- Registration requires username, full name, unique lowercase email and a
  confirmed password. Passwords are hashed; password/remember-token fields
  are hidden from serialization and never displayed in forms.
- Blank password edits retain the current hash. Changing your own password
  requires your current password. The active account cannot delete itself.
- TFA7 avatar validation, 256px JPEG preparation and file cleanup are retained.
- All authenticated accounts have the same account-management permissions,
  as requested by TFA8. Registration is open; no administrator role is added.
- Breeze login throttling, session regeneration, logout and reset-token
  handling are retained. Email verification is not enforced by this assignment.
- The default mail transport is `log` for local development. Password reset
  delivery requires working mail settings; no outgoing email service is supplied.

## Verification

`VERIFY.ps1` checks dependencies, built assets, PHP/compiled Blade syntax,
routes and the supplied feature tests. Tests force an isolated in-memory SQLite
database; they do not use the live database. There are 57 application tests
covering authentication, guest access, both resources and prior TFA6/TFA7 behavior.
See `docs/TFA8-TEST-CHECKLIST.md` for the manual browser checks and review limits.

## Deployment and submission

Follow `docs/TFA8-INFINITYFREE.md`; TFA8 needs built `public/build` assets.
Keep migrations and seeders in Git. Do not commit `.env`, local SQLite files,
backup folders, uploads, logs, vendor or node_modules. Commit BOTH dependency
lock files after installation. Never put a real password in the README.
Update the two submission links above after local and hosted verification.

## References

- [Laravel resource controllers](https://laravel.com/docs/13.x/controllers#resource-controllers)
- [Laravel route middleware](https://laravel.com/docs/13.x/routing#route-group-middleware)
- [Laravel Breeze source and releases](https://github.com/laravel/breeze)
- [Laravel starter kits](https://laravel.com/docs/13.x/starter-kits)
