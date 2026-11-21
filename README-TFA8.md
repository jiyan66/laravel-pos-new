# TFA8 upgrade notes

See the root README.md for installation, behavior and submission links, and
`docs/TFA8-INFINITYFREE.md` for the hosting upgrade. This update builds on the
completed TFA7 source; it does not reset customer/user records or replace the
existing application key. The original migrations/seeders remain in the repo.

Laravel Breeze is installed as a development dependency, then its actual Blade
scaffolder is run. Its session controller, LoginRequest and auth routes remain
generated Breeze code. Registration is adapted to username/full_name.
`docs/BREEZE-INSTALLATION.json` records the installed version and retained-file
hashes generated on your computer. The copied Breeze license is retained in docs.

The installer removes only Breeze's freshly generated default Auth/Profile
TEST files, because they expect a different user schema. POS-specific auth,
resource and regression tests replace them; no authentication bypass is added.
