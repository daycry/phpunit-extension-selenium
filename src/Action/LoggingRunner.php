<?php

declare(strict_types=1);

namespace Daycry\PHPUnit\Selenium\Action;

use Closure;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Throwable;

final readonly class LoggingRunner implements ActionRunner
{
    public function __construct(
        private ActionRunner $inner,
        private LoggerInterface $logger = new NullLogger(),
    ) {
    }

    public function run(string $name, Closure $action): mixed
    {
        $startedAt = microtime(true);
        $this->logger->debug('selenium.action.start', ['action' => $name]);

        try {
            $result = $this->inner->run($name, $action);
        } catch (Throwable $e) {
            $this->logger->warning('selenium.action.fail', [
                'action' => $name,
                'duration_ms' => $this->durationMs($startedAt),
                'exception' => $e::class,
                'message' => $e->getMessage(),
            ]);
            throw $e;
        }

        $this->logger->debug('selenium.action.ok', [
            'action' => $name,
            'duration_ms' => $this->durationMs($startedAt),
        ]);

        return $result;
    }

    private function durationMs(float $startedAt): int
    {
        return (int) round((microtime(true) - $startedAt) * 1000);
    }
}
