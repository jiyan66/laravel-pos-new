# IT0049 TFA7 Laravel POS updates

TFA7 extends the TFA6 application with create/edit forms and user avatars.
Refer to the original README for base setup; follow this file for the upgrade.

## New routes

| Method | URL | Purpose |
| --- | --- | --- |
| GET | /customers/create | Create Customer form |
| POST | /customers | Save validated customer |
| GET | /customers/{customer}/edit | Prefilled Customer edit form |
| PUT | /customers/{customer} | Save validated customer changes |
| GET | /users/create | Create User form |
| POST | /users | Save validated user |
| GET | /users/{user}/edit | Prefilled User edit form with avatar upload |
| PUT | /users/{user} | Save user changes and optional avatar |
| GET | /avatars/{filename} | Stream one generated JPEG from the public disk |

The existing four routes remain available. Forms include CSRF tokens and edit
forms spoof PUT through POST. Route model binding returns 404 for missing rows.
Form Requests isolate server validation from controllers. Blade old() preserves
text after an error; error summaries and field messages are displayed.

## Validation

| Input | Rule |
| --- | --- |
| Customer full_name | Required string, at most 100 characters |
| Customer email | Required valid email, at most 100 characters |
| Customer phone | Optional string, at most 20 characters |
| User username | Required string, at most 50 characters, unique except the bound user on update |
| User full_name | Required string, at most 100 characters |
| Avatar on edit | Optional real JPG/PNG image, permitted extension, at most 2048 KB and 3000 by 3000 pixels |

Customer email is not required to be unique by the brief, so duplicate customer
emails are not rejected by an invented uniqueness requirement.

## Image preparation

Intervention Image v3 with GD crops/resizes to 256 by 256 and re-encodes to JPEG
at quality 85 with metadata stripping. The original upload is not stored.
Storage::disk('public') writes only the prepared bytes under a random UUID name.
The database stores a path such as avatars/uuid.jpg, not image bytes or a URL.
The package choice must match the instructor's approval; no package was named
in the supplied assignment.

A text-only edit retains the old avatar. A replacement is written first, the
profile is updated transactionally, and the old managed file is removed after
commit. If the database save fails, the new unused image is removed. Cleanup
will not delete arbitrary paths. Storage errors return an avatar validation
message. Invalid inputs never reach persistence.

The public disk is served through a filename-restricted avatar route, avoiding
symlink deployment requirements. A model accessor supplies a placeholder for
unset or missing images. Path and extension restrictions prevent the avatar
endpoint from serving arbitrary files from storage.

## Local upgrade

```powershell
$env:Path = "C:\php;" + $env:Path
php --ri gd
composer update intervention/image --with-dependencies
php artisan optimize:clear
php artisan migrate
powershell -NoProfile -ExecutionPolicy Bypass -File .\VERIFY.ps1
php artisan serve --port=8001
```

Enable extension=gd in the loaded php.ini first. Do not run migrate:fresh.
Do not replace .env or discard its existing APP_KEY. No storage:link is needed.
Review docs/TFA7-INFINITYFREE.md for the hosting upgrade.

## Submission links

Add the actual GitHub repository URL and hosted website URL here before submitting.
Commit the updated composer.lock after installing Intervention Image. Keep .env,
SQLite data, vendor, runtime uploads, logs, and caches out of GitHub.

## Verification status

The test suite and VERIFY.ps1 are supplied for execution on PHP-equipped systems.
The authoring workspace does not contain PHP/Composer, so PHPUnit, PHP lint, and
Blade runtime execution were not run there. Check the local verifier and the
included GitHub Actions job before reporting passed tests.
