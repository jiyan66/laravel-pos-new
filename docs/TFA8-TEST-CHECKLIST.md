# TFA8 verification and submission checklist

## Automated checks on your machine

Run `powershell -NoProfile -ExecutionPolicy Bypass -File .\VERIFY.ps1` in the
project root after installing. It stops at the first failed command.
The installer runs it automatically. Expected application feature test total: 57.
The tests use forced in-memory SQLite, not your configured application database.

Coverage includes login/registration validation, hashed passwords, intended
redirects, session regeneration, logout, throttled login attempts, reset-token
expiry, required current password, all seven actions' auth middleware, guest
write rejection, details/deletion, safe legacy provisioning, user/password
updates, and TFA6/TFA7 listing/validation/avatar regressions.

## Browser checks

1. In a private/logged-out window open `/customers`, `/users`, and their create,
   show and edit URLs. Expect a redirect to `/login`, with no account data shown.
2. Register a new username/email, enter a confirmed password of at least eight
   characters, and verify you reach the customer list. Duplicate/invalid fields
   must show validation messages. Log out and log back in with the email.
3. Verify `/customers` and `/users` now open normally. Create, view, edit and
   delete a temporary customer. Repeat with a temporary user. The active account
   should offer no delete action and reject a direct self-delete request.
4. Edit a seeded older account: add a unique email and initial password. Confirm
   its ID/avatar were preserved. Log in as that account using its new email.
5. Verify keeping an existing password blank leaves it unchanged. A new
   password needs confirmation; your own password change also needs the current
   password. Password fields must never be prefilled or redisplayed after errors.
6. Upload a valid JPG/PNG <=2MB and <=3000x3000; verify the prepared avatar.
   Text-only updates keep the old image. Bad uploads show validation messages.
7. Log out using the POST form; revisiting either listing should require login.
8. Repeat on the hosted HTTPS site with a separate test account. Check that
   both login styling and the original POS styling load without a Vite error.
9. Keep APP_DEBUG=false on hosting. If a failure occurs, inspect private logs.
   Fix the failure before reporting the hosted application as working.

## Review limits for the supplied update archive

The archive was checked for PHP lexical/delimiter consistency, Blade/link
references, JSON/XML/YAML structure, route/controller coverage, safe update
contents and SQL preservation using an explicitly SQLite-adapted SQL execution.
PHP/Composer/PowerShell, Blade execution, PHPUnit, npm build and a MySQL server
were not available in the preparation workspace. They were NOT reported as
passed. The installer performs the actual dependency install, build and PHP
checks on your machine. A live site and GitHub push are still user actions.

## Submission

Commit the complete upgraded source, generated Breeze source, both lock files,
new migration, existing seeders, and documentation. Add the actual repository
and hosted links to README.md, verify both links, then submit both links.
