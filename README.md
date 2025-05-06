Выполнить миграции:
```shell
php run.php db:migrate
```

Встроенный сервер:
```shell
php -S localhost:8000
```

Исправить форматирование:
```shell
php php-cs-fixer.phar fix
phpstan analyse
```

Загрузка пользователей:
```
http://localhost:8000/user-importer/import?file=Данные%20для%20тестового.csv
```
Обход пользователей и добавление в очередь рассылки:
```
http://localhost:8000/newsletter/add
```