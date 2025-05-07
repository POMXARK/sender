<?php

declare(strict_types=1);

namespace App;

final class AppConfig
{
    public const DB_NAME = 'users.db';
    public const JOBS = [Jobs\SendEmailJob::class];
}
