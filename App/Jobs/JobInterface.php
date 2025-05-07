<?php

declare(strict_types=1);

namespace App\Jobs;

interface JobInterface
{
    /**
     * @param array<mixed> $data
     */
    public function handle(array $data): void;
}
