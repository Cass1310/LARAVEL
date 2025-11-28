@echo off
cd /d "%~dp0"
echo Ejecutando backup programado: %date% %time%
php artisan backup:run --only-db >> storage/logs/backup.log 2>&1
echo Backup completado: %date% %time% >> storage/logs/backup.log