$ErrorActionPreference = 'Stop'
Set-Location -LiteralPath $PSScriptRoot
if (Test-Path 'C:\php\php.exe') { $env:Path = 'C:\php;' + $env:Path }
& php --ri gd | Out-Null
if ($LASTEXITCODE -ne 0 -and (Test-Path 'C:\php\conf.d\gd.ini')) { $env:PHP_INI_SCAN_DIR = 'C:\php\conf.d'; & php --ri gd | Out-Null }
if ($LASTEXITCODE -ne 0) { throw 'Enable GD in the PHP configuration used by this terminal.' }
& composer validate --no-check-publish
if ($LASTEXITCODE -ne 0) { throw 'Composer validation failed.' }
& composer check-platform-reqs
if ($LASTEXITCODE -ne 0) { throw 'Composer platform requirements failed.' }
& composer show laravel/breeze --no-ansi
if ($LASTEXITCODE -ne 0) { throw 'Install the development dependencies, including Laravel Breeze.' }
foreach ($required in @('routes\auth.php','app\Http\Requests\Auth\LoginRequest.php','app\Http\Controllers\Auth\AuthenticatedSessionController.php','public\build\manifest.json','docs\BREEZE-INSTALLATION.json')) {
    if (-not (Test-Path -LiteralPath $required)) { throw "Missing $required. Finish APPLY-TFA8.ps1 first." }
}
$folders = 'app', 'bootstrap', 'config', 'database', 'routes', 'tests'
foreach ($folder in $folders) {
    Get-ChildItem $folder -Recurse -File -Filter '*.php' | ForEach-Object {
        & php -l $_.FullName
        if ($LASTEXITCODE -ne 0) { throw "PHP syntax error: $($_.FullName)" }
    }
}
& php -l public/index.php
if ($LASTEXITCODE -ne 0) { throw 'public/index.php has a syntax error.' }
& php artisan view:cache
if ($LASTEXITCODE -ne 0) { throw 'Blade compilation failed.' }
Get-ChildItem 'storage/framework/views' -File -Filter '*.php' | ForEach-Object {
    & php -l $_.FullName
    if ($LASTEXITCODE -ne 0) { throw "Compiled Blade syntax error: $($_.FullName)" }
}
& php artisan view:clear
if ($LASTEXITCODE -ne 0) { throw 'Compiled view cleanup failed.' }
& composer test
if ($LASTEXITCODE -ne 0) { throw 'Automated tests failed.' }
& php artisan route:list --except-vendor
if ($LASTEXITCODE -ne 0) { throw 'Route inspection failed.' }
Write-Host 'TFA8 verification commands passed.' -ForegroundColor Green
