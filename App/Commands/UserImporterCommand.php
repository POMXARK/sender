<?php

declare(strict_types=1);

namespace App\Commands;

use App\AppConfig;
use App\Common\Database;

/**
 * Класс для импорта пользователей из CSV-файлов.
 */
final class UserImporterCommand
{
    /**
     * Метод для импорта пользователей из CSV-файла.
     *
     * @param string $filePath путь к файлу CSV
     *
     * @return array<string> возвращает массив с результатом импорта
     */
    public static function execute(string $filePath): array
    {
        $db = new Database(AppConfig::DB_NAME);
        $pdo = $db->getPDO();

        // Установите кодировку UTF-8 для соединения с базой данных
        $pdo->exec("PRAGMA encoding = 'UTF-8'");

        // Проверка формата файла
        if ('csv' !== pathinfo($filePath, PATHINFO_EXTENSION)) {
            http_response_code(400);

            return ['error' => 'Неверный формат файла.'];
        }

        // Получаем полный путь к файлу
        $realPath = realpath($filePath);
        if (false === $realPath) {
            http_response_code(400);

            return ['error' => 'Файл не найден.'];
        }

        // Чтение файла CSV
        if (($handle = fopen($realPath, 'r')) !== false) {
            // Установите кодировку UTF-8 для чтения файла
            stream_filter_prepend($handle, 'convert.iconv.UTF-8/UTF-8');

            while (($data = fgetcsv($handle, 1000)) !== false) {
                // Убедитесь, что $data является массивом
                if (is_array($data)) {
                    // Удаление пустых значений из массива
                    $data = array_filter($data);

                    // Проверка на наличие двух колонок
                    if (2 === count($data)) {
                        $number = trim($data[0]);
                        $name = trim($data[1]);

                        // Вставка пользователя в базу данных
                        $stmt = $pdo->prepare('INSERT OR IGNORE INTO users (number, name) VALUES (:number, :name)');
                        $stmt->bindParam(':number', $number);
                        $stmt->bindParam(':name', $name);
                        $stmt->execute();
                    }
                }
            }
            fclose($handle);

            // Возвращаем успешный ответ
            return ['success' => 'true'];
        }

        return ['success' => 'false'];
    }
}
