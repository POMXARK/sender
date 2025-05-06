<?php

declare(strict_types=1);

namespace App\Common;

use DirectoryIterator;
use RuntimeException;

final class MigrationManager
{
    private Database $database;

    public function __construct(Database $database)
    {
        $this->database = $database;
        $this->createMigrationsTable(); // Создаем таблицу при инициализации
    }

    public function migrate(): void
    {
        $migrationFiles = $this->getMigrationFiles();

        foreach ($migrationFiles as $file) {
            $migrationName = basename($file, '.php');

            if (!$this->migrationExists($migrationName)) {
                require_once $file;
                $className = $this->getClassNameFromFile($file);
                $migration = new $className();

                if (method_exists($migration, 'up')) {
                    $migration->up($this->database->getPDO());
                } else {
                    throw new RuntimeException("Метод 'up' не найден в классе '$className'.");
                }

                $this->recordMigration($migrationName);
            }
        }
    }

    public function rollback(int $steps = 1): void
    {
        for ($i = 0; $i < $steps; ++$i) {
            $lastMigration = $this->getLastMigration();

            if ($lastMigration) {
                // Получаем имя файла миграции
                $migrationFile = $this->getMigrationFile($lastMigration);

                // Проверяем, существует ли файл миграции
                if (file_exists($migrationFile)) {
                    require_once $migrationFile; // Загружаем файл миграции

                    // Используем метод getClassNameFromFile для получения имени класса
                    $className = $this->getClassNameFromFile($migrationFile);

                    if (class_exists($className)) { // Проверяем, существует ли класс
                        $migration = new $className();

                        if (method_exists($migration, 'down')) {
                            $migration->down($this->database->getPDO());
                        } else {
                            throw new RuntimeException("Метод 'down' не найден в классе '$className'.");
                        }

                        $this->removeMigration($lastMigration);
                    } else {
                        throw new RuntimeException("Класс миграции '$className' не найден.");
                    }
                } else {
                    throw new RuntimeException("Файл миграции '$migrationFile' не найден.");
                }
            } else {
                echo "Нет миграций для отката.\n";
                break; // Выходим из цикла, если нет миграций
            }
        }
    }

    private function createMigrationsTable(): void
    {
        $pdo = $this->database->getPDO();
        $pdo->exec('
            CREATE TABLE IF NOT EXISTS migrations (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                migration TEXT NOT NULL UNIQUE
            )
        ');
    }

    /**
     * @return array<string> возвращает массив файлов миграций
     */
    private function getMigrationFiles(): array
    {
        $files = [];
        $dirPath = __DIR__.'/../../migrations';
        $dir = new DirectoryIterator($dirPath);
        foreach ($dir as $fileinfo) {
            if ($fileinfo->isFile() && 'php' === $fileinfo->getExtension()) {
                $files[] = $fileinfo->getPathname();
            }
        }

        return $files;
    }

    private function getClassNameFromFile(string $file): string
    {
        // Извлекаем имя файла без расширения
        $migrationName = basename($file, '.php');

        // Преобразуем имя файла в имя класса
        // Например, "2025_05_06_000000_create_users_table" в "CreateUsersTable"
        $className = preg_replace('/^(\d+_\d+_\d+_\d+)_/', '', $migrationName); // Убираем временную метку
        $className = str_replace('_', '', ucwords((string) $className, '_')); // Преобразуем в CamelCase

        return 'App\Migrations\\'.$className; // Возвращаем полное имя класса
    }

    private function getMigrationFile(string $migration): string
    {
        $dirPath = __DIR__.'/../../migrations';

        return $dirPath.'/'.$migration.'.php';
    }

    private function migrationExists(string $migration): bool
    {
        $stmt = $this->database->getPDO()->prepare('SELECT COUNT(*) FROM migrations WHERE migration = ?');
        if ($stmt->execute([$migration])) {
            return (bool) $stmt->fetchColumn(); // Приводим к булевому типу
        }

        return false; // Если запрос не выполнен, возвращаем false
    }

    private function recordMigration(string $migration): void
    {
        $stmt = $this->database->getPDO()->prepare('INSERT INTO migrations (migration) VALUES (?)');
        if (!$stmt->execute([$migration])) {
            throw new RuntimeException("Не удалось записать миграцию '$migration'.");
        }
    }

    private function removeMigration(string $migration): void
    {
        $stmt = $this->database->getPDO()->prepare('DELETE FROM migrations WHERE migration = ?');
        if (!$stmt->execute([$migration])) {
            throw new RuntimeException("Не удалось удалить миграцию '$migration'.");
        }
    }

    private function getLastMigration(): ?string
    {
        $stmt = $this->database->getPDO()->query('SELECT migration FROM migrations ORDER BY id DESC LIMIT 1');

        if (false !== $stmt) { // Проверяем, что $stmt не false
            $result = $stmt->fetchColumn();

            return false !== $result ? (string) $result : null; // Возвращаем строку или null
        }

        return null; // Если запрос не выполнен, возвращаем null
    }

    public function createMigration(string $name): void
    {
        $timestamp = date('Y_m_d_His');
        $fileName = "{$timestamp}_{$name}.php";
        $filePath = __DIR__."/../../migrations/{$fileName}";

        // Преобразование имени из snake_case в CamelCase
        $className = str_replace(' ', '', ucwords(str_replace('_', ' ', $name)));

        $template = <<<EOT
            <?php
            
            declare(strict_types=1);
            
            namespace App\Migrations;
            
            use PDO;
            
            class {$className}
            {
                public function up(PDO \$pdo): void
                {
                    // Логика миграции
                }
            
                public function down(PDO \$pdo): void
                {
                    // Логика отката
                }
            }
            EOT;

        file_put_contents($filePath, $template);
    }
}
