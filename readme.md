# Sistema de ventas Zisco

## REQUERIMIENTOS
- Laravel 9.*
- PHP > 8.0
- MySql o MariaDB

## COMANDOS DE INSTALACIÓN
- cp .env.example .env
- composer install
- php artisan key:generate
- php artisan storage:link
- php artisan migrate --seed
- php artisan config:cache && php artisan config:clear && php artisan cache:clear
