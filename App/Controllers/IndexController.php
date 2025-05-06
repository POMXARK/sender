<?php

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
