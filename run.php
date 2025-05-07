<?php

declare(strict_types=1);

require_once 'App/Common/Database.php';
require_once 'App/Common/MigrationManager.php';
require_once 'App/AppConfig.php';
require_once 'App/Common/QueueManager.php';

require_once 'autoload.php'; // Подключаем файл автозагрузки

use App\AppConfig;
use App\Common\Database;
use App\Common\MigrationManager;
use App\Common\QueueManager;

$queueManager = new QueueManager(AppConfig::JOBS);

// Получаем аргумент командной строки
$command = $argv[1] ?? null;
$steps = 1; // По умолчанию откатываем 1 миграцию

// Проверяем наличие параметра --step
if (isset($argv[2]) && 0 === strpos($argv[2], '--step=')) {
    $stepValue = substr($argv[2], strlen('--step='));
    if (is_numeric($stepValue) && (int) $stepValue > 0) {
        $steps = (int) $stepValue; // Устанавливаем количество шагов для отката
    }
}

$db = new Database(AppConfig::DB_NAME); // Создаем экземпляр базы данных
$migrationManager = new MigrationManager($db); // Создаем экземпляр менеджера миграций

// Выполняем команду в зависимости от аргумента
switch ($command) {
    case 'db:migrate':
        $migrationManager->migrate(); // Выполняем миграции
        echo "Миграции выполнены успешно.\n";
        break;

    case 'db:rollback':
        $migrationManager->rollback($steps); // Откатываем указанное количество миграций
        echo "Последние $steps миграций(и) откатены успешно.\n";
        break;

    case 'db:create':
        if (isset($argv[2])) {
            $migrationName = $argv[2]; // Получаем имя миграции из аргументов
            $migrationManager->createMigration($migrationName); // Создаем миграцию
            echo "Миграция '$migrationName' создана успешно.\n";
        } else {
            echo "Не указано имя миграции. Используйте 'db:create MigrationName'.\n";
        }
        break;
    case 'queue:run':
        // Запуск конкретного класса задачи
        if (isset($argv[2]) && class_exists($argv[2]) && is_subclass_of($argv[2], 'App\Jobs\JobInterface')) {
            $jobClass = $argv[2];
            $queueManager->push($jobClass, []); // Здесь можно передать данные, если нужно
            $queueManager->listen(); // Запускаем обработку очереди
            echo "Обработка очереди завершена для класса {$jobClass}.\n";
        } else {
            echo "Некорректный класс задачи. Убедитесь, что он существует и реализует интерфейс JobInterface.\n";
        }
        break;

    default:
        echo "Неверная команда. Используйте 'db:migrate', 'db:rollback', 'db:create', 'queue:add' или 'queue:run'.\n";
        break;
}
