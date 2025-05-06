<?php

declare(strict_types=1);

require_once 'autoload.php'; // Подключаем файл автозагрузки

header('Content-Type: application/json; charset=utf-8'); // Устанавливаем заголовок для JSON
header('X-Content-Type-Options: nosniff'); // Защита от MIME-типов
header('X-Frame-Options: DENY'); // Защита от Clickjacking
header("Content-Security-Policy: default-src 'self';"); // Защита от XSS

$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Убедимся, что $requestUri - это строка, если нет, используем пустую строку
$requestUri = is_string($requestUri) ? $requestUri : '';

$requestMethod = $_SERVER['REQUEST_METHOD'];

$uriParts = explode('/', trim($requestUri, '/'));
$controllerName = !empty($uriParts[0]) ? str_replace('-', '', ucwords($uriParts[0], '-')).'Controller' : 'IndexController';
$actionName = !empty($uriParts[1]) ? $uriParts[1].'Action' : 'indexAction';
$fullControllerName = 'App\\Controllers\\'.$controllerName;

if (class_exists($fullControllerName)) {
    $controller = new $fullControllerName();

    if (method_exists($controller, $actionName)) {
        $response = $controller->$actionName(); // Получаем ответ от контроллера
        echo json_encode($response, JSON_UNESCAPED_UNICODE); // Возвращаем JSON-ответ
    } else {
        http_response_code(404);
        error_log(date('Y-m-d H:i:s')." - Error: Метод не найден. - Path: $requestUri - Method: $actionName".PHP_EOL);
    }
} else {
    http_response_code(404);
    error_log(date('Y-m-d H:i:s')." - Error: Класс не найден. - Path: $requestUri - Class: $fullControllerName".PHP_EOL);
}
