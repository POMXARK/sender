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
curl -X POST http://localhost:8000/newsletter/add \
     -H "Content-Type: application/json" \
     -d '{"title": "Название рассылки", "message": "Текст рассылки"}'
```

Запуск очереди рассылки
```shell
php run.php  queue:run "App\Jobs\SendEmailJob"
```

systemd
```shell
/etc/systemd/system/queue-worker.service
sudo systemctl daemon-reload
sudo systemctl start queue-worker
sudo systemctl enable queue-worker 
sudo systemctl status queue-worker
```

crontab
```shell
@reboot /usr/bin/php /path/to/your/run.php queue:run App\Jobs\SendEmailJob > /dev/null 2>&1 &
```