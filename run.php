<?php

declare(strict_types=1);

require_once 'App/Common/Database.php';
require_once 'App/Common/MigrationManager.php';
require_once 'App/AppConfig.php';

use App\AppConfig;
use App\Common\Database;
use App\Common\MigrationManager;

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

    default:
        echo "Неверная команда. Используйте 'db:migrate' для выполнения миграций, 'db:rollback' для отката или 'db:create' для создания новой миграции.\n";
        break;
}
