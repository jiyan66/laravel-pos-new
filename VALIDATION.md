# Validation record

Checks completed while producing these packages:

- Composer JSON and PHPUnit XML parse successfully.
- PHP source passed lightweight lexical/delimiter checks. This is not PHP lint.
- Named routes point to existing controllers and methods; Blade references and
  navigation route names are consistent.
- Five customer/user seed rows match the SQL import.
- A SQLite-adapted execution of the SQL confirms seed counts, username uniqueness,
  and preservation of records when adding the nullable avatar field.
- The TFA7 patch applies to the exact TFA6 baseline and reproduces all update files.
- TFA7 contains only added/changed files; it does not overwrite .env, databases,
  uploads, seeders, base migrations, or composer.lock.

Runtime checks not executed here: PHP lint, Composer dependency resolution,
Blade runtime compilation, PHPUnit, GD/Intervention execution, browser rendering,
actual MySQL import, and hosted deployment. PHP and Composer are not installed
in the authoring environment. Run SETUP.ps1 and VERIFY.ps1 as documented.
The GitHub workflow supplies another PHP-equipped verification route after push.

The baseline has 8 feature tests; applying TFA7 adds 21, for 29 total.
Intervention Image v3 is the selected package, pending compatibility with the
instructor's approved-package guidance.
