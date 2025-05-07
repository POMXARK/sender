<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\NewsletterService;

/**
 * Контроллер для управления рассылками.
 */
final class NewsletterController
{
    private NewsletterService $newsletterService;

    /**
     * Конструктор контроллера.
     */
    public function __construct()
    {
        $this->newsletterService = new NewsletterService();
    }

    /**
     * Метод для добавления новой рассылки.
     *
     * @return array<string, string>
     */
    public function addAction(): array
    {
        // Получение данных из тела запроса
        $json = file_get_contents('php://input');

        // Проверка, что $json является строкой
        if (false === $json) {
            return ['error' => 'Не удалось получить данные из запроса.'];
        }

        // Декодирование JSON
        $data = json_decode($json, true);

        // Проверка на ошибки декодирования JSON
        if (null === $data && JSON_ERROR_NONE !== json_last_error()) {
            return ['error' => 'Неверный формат JSON.'];
        }

        // Проверка наличия обязательных параметров
        if (!is_array($data) || !isset($data['title'], $data['message'])) {
            return ['error' => 'Параметры title и message обязательны.'];
        }

        // Убедитесь, что параметры являются строками
        $title = (string) $data['title'];
        $message = (string) $data['message'];

        // Добавление рассылки через сервис
        $this->newsletterService->addNewsletter($title, $message);

        return ['success' => 'Рассылка успешно добавлена в очередь.'];
    }
}
