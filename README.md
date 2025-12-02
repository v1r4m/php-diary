## zero knowledge diary
* Laravel 10 (PHP 8.2)
* SQLite

```
docker-compose up -d --build
docker-compose exec app composer install --working-dir=/var/www
docker-compose exec app php artisan key:generate
docker-compose exec app php artisan migrate
```