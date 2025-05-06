<?php

declare(strict_types=1);

// Файл autoload.php
spl_autoload_register(function ($class) {
    // Преобразование пространства имен в путь к файлу
    $file = __DIR__.'/'.str_replace('\\', '/', $class).'.php';

    // Проверка существования файла и его подключение
    if (file_exists($file)) {
        require_once $file;
    } else {
        error_log('Файл не найден: '.$file); // Логирование отсутствующих файлов
    }
});
