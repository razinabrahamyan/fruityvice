# fruityvice

## Project setup
```
cp .env.simple to .env. Change DB and Email data  
```
```
composer install
```
```
php bin/console doctrine:migrations:migrate
```
```
php bin/console fruits:fetch
```

```
symfony server:start
```
