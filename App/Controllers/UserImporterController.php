<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Commands\UserImporterCommand;

/**
 * Контроллер для импорта пользователей из CSV-файлов.
 */
final class UserImporterController
{
    /**
     * Метод для импорта пользователей из указанного файла.
     *
     * @return array<string, string> ответ с информацией об успешном или неуспешном импорте
     */
    public function importAction(): array
    {
        // Проверяем, указан ли параметр file
        if (!isset($_GET['file'])) {
            http_response_code(400);

            return ['error' => 'Параметр file не указан.'];
        }

        // Получаем путь к файлу из параметров запроса
        $filePath = $_GET['file'];

        // Проверяем, существует ли файл
        if (!file_exists($filePath)) {
            http_response_code(404);

            return ['error' => 'Файл не найден.'];
        }

        // Получаем результат выполнения команды импорта
        return UserImporterCommand::execute($filePath);
    }
}
