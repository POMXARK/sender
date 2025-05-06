<?php

declare(strict_types=1);

namespace App\Common;

use PDO;

/**
 * Класс для работы с базой данных SQLite.
 */
final class Database
{
    private PDO $pdo;

    /**
     * Конструктор класса.
     *
     * @param string $dbFile путь к файлу базы данных
     */
    public function __construct(string $dbFile)
    {
        // Подключение к базе данных SQLite
        $this->pdo = new PDO("sqlite:$dbFile");
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    /**
     * Метод для получения объекта PDO.
     *
     * @return PDO объект PDO для работы с базой данных
     */
    public function getPDO(): PDO
    {
        return $this->pdo;
    }
}
