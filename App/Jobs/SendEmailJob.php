<?php

declare(strict_types=1);

namespace App\Jobs;

use App\AppConfig;
use App\Common\Database;
use PDO;

final class SendEmailJob implements JobInterface
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
     * @param array<mixed> $data
     */
    public function handle(array $data): void
    {
        while (true) {
            // Извлечение записи из таблицы newsletters
            $stmt = $this->pdo->prepare('SELECT * FROM newsletters LIMIT 1');
            $stmt->execute();
            $newsletter = $stmt->fetch(PDO::FETCH_ASSOC);

            // Если записей нет, выходим из цикла
            if (false === $newsletter) {
                sleep(5); // Ожидание перед следующей попыткой
                continue;
            }

            // Проверка наличия ключей
            if (
                !is_array($newsletter) // Убедитесь, что это массив
                || !array_key_exists('user_id', $newsletter)
                || !array_key_exists('title', $newsletter)
                || !array_key_exists('message', $newsletter)
                || !array_key_exists('sent_at', $newsletter)
                || !array_key_exists('id', $newsletter)
            ) {
                // Обработка ошибки: данные некорректны
                continue; // Или вы можете выбросить исключение
            }

            // Приведение типов
            $userId = (int) $newsletter['user_id'];
            $title = (string) $newsletter['title'];
            $message = (string) $newsletter['message'];
            $sentAt = (string) $newsletter['sent_at'];
            $id = (int) $newsletter['id'];

            // Отправка email
            $this->sendEmail($userId, $title, $message);

            // Логирование
            $this->logSentEmail($userId, $title, $message, $sentAt);

            // Удаление записи из базы данных
            $this->deleteNewsletter($id);
        }
    }

    private function sendEmail(int $userId, string $title, string $message): void
    {
        // Здесь должна быть ваша логика отправки email
        // Например, использование функции mail() или сторонней библиотеки
        // mail($to, $title, $message);
    }

    private function logSentEmail(int $userId, string $title, string $message, string $sentAt): void
    {
        // Логирование отправленного email
        echo "Отправлено: user_id=$userId, title=$title, message=$message, sent_at=$sentAt\n";
        // Вы можете также записывать логи в файл или в другую систему логирования
    }

    private function deleteNewsletter(int $id): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM newsletters WHERE id = :id');
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
    }
}
