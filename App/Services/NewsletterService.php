<?php

declare(strict_types=1);

namespace App\Services;

use App\AppConfig;
use App\Common\Database;
use PDO;
use RuntimeException;

/**
 * Класс для управления рассылками.
 */
class NewsletterService
{
    private PDO $pdo;

    /**
     * Конструктор класса.
     */
    public function __construct()
    {
        $this->pdo = (new Database(AppConfig::DB_NAME))->getPDO();
    }

    /**
     * Метод для добавления новой рассылки в очередь.
     *
     * @param string $title   заголовок рассылки
     * @param string $message текст рассылки
     */
    public function addNewsletter(string $title, string $message): void
    {
        // Получение всех пользователей
        $stmt = $this->pdo->query('SELECT id FROM users');

        if (false === $stmt) {
            throw new RuntimeException('Ошибка выполнения запроса на получение пользователей.');
        }

        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Обход пользователей и добавление в очередь рассылки
        foreach ($users as $user) {
            $userId = $user['id'];

            // Проверка, была ли уже отправлена рассылка этому пользователю
            $checkStmt = $this->pdo->prepare('SELECT COUNT(*) FROM newsletters WHERE user_id = :user_id AND title = :title');
            $checkStmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $checkStmt->bindParam(':title', $title);
            $checkStmt->execute();

            if (0 == $checkStmt->fetchColumn()) {
                // Добавление в таблицу рассылок
                $insertStmt = $this->pdo->prepare('INSERT INTO newsletters (user_id, title, message) VALUES (:user_id, :title, :message)');
                $insertStmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
                $insertStmt->bindParam(':title', $title);
                $insertStmt->bindParam(':message', $message);
                $insertStmt->execute();
                $this->sendMessage($title, $message);
            }
        }
    }

    private function sendMessage(string $title, string $message): void
    {
    }
}
