$ErrorActionPreference = 'Stop'
Set-Location $PSScriptRoot
if (Test-Path 'C:\php\php.exe') { $env:Path = 'C:\php;' + $env:Path }
& composer validate --no-check-publish
if ($LASTEXITCODE -ne 0) { throw 'Composer validation failed.' }
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
if ($LASTEXITCODE -ne 0) { throw 'Blade view compilation failed.' }
Get-ChildItem 'storage/framework/views' -File -Filter '*.php' | ForEach-Object {
    & php -l $_.FullName
    if ($LASTEXITCODE -ne 0) { throw "Compiled Blade syntax error: $($_.FullName)" }
}
& php artisan view:clear
if ($LASTEXITCODE -ne 0) { throw 'Compiled view cleanup failed.' }
& composer test
if ($LASTEXITCODE -ne 0) { throw 'Automated tests failed.' }
Write-Host 'All verification commands passed.' -ForegroundColor Green
