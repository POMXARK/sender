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
        // Параметры рассылки
        $title = 'Название рассылки';
        $message = 'Текст рассылки';

        // Добавление рассылки через сервис
        $this->newsletterService->addNewsletter($title, $message);

        return ['success' => 'Рассылка успешно добавлена в очередь.'];
    }
}
