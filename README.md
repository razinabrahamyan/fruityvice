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
add from and to mails in FruitsFetchCommand.php
```
```
php bin/console fruits:fetch
```

```
php bin/console lexik:jwt:generate-keypair
```

```
symfony server:start
```
