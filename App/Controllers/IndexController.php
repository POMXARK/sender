<?php

declare(strict_types=1);

namespace App\Controllers;

final class IndexController
{
    /**
     * @return array<string, string>
     */
    public function indexAction(): array
    {
        return ['message' => 'Добро пожаловать на главную страницу!'];
    }
}
