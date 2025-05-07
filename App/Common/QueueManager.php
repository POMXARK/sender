<?php

declare(strict_types=1);

namespace App\Common;

use App\Jobs\JobInterface;
use InvalidArgumentException;

/**
 * Класс для управления очередью задач.
 */
class QueueManager
{
    /**
     * @var array<mixed>
     *                   Список задач в очереди
     */
    private array $queue = [];

    /**
     * @var string[] Список зарегистрированных классов задач
     */
    private array $jobs = [];

    /**
     * @param string[] $configPath Массив классов задач
     */
    public function __construct(array $configPath)
    {
        $this->loadJobs($configPath);
    }

    /**
     * @param string[] $configPath Массив классов задач
     */
    private function loadJobs(array $configPath): void
    {
        foreach ($configPath as $jobClass) {
            if (class_exists($jobClass) && is_subclass_of($jobClass, JobInterface::class)) {
                $this->jobs[] = $jobClass;
            }
        }
    }

    /**
     * @param string               $jobClass Класс задачи
     * @param array<string, mixed> $data     Данные для задачи
     */
    public function push(string $jobClass, array $data): void
    {
        if (in_array($jobClass, $this->jobs)) {
            $this->queue[] = [
                'job' => new $jobClass(),
                'data' => $data,
            ];
        } else {
            throw new InvalidArgumentException("Job class $jobClass is not registered.");
        }
    }

    public function listen(): void
    {
        while (!empty($this->queue)) {
            /** @var array{job: JobInterface, data: mixed} $jobData */
            $jobData = array_shift($this->queue);

            /** @var JobInterface $job */
            $job = $jobData['job'];

            // Убедитесь, что $data имеет тип array
            $data = $jobData['data'];

            // Если handle ожидает массив, убедитесь, что $data является массивом
            if (!is_array($data)) {
                throw new InvalidArgumentException('Data must be an array.');
            }

            $job->handle($data);
        }
    }
}
