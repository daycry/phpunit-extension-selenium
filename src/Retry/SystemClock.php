<?php

declare(strict_types=1);

namespace Daycry\PHPUnit\Selenium\Retry;

final class SystemClock implements Clock
{
    public function nowMs(): int
    {
        return (int) (microtime(true) * 1000);
    }

    public function sleepMs(int $ms): void
    {
        if ($ms <= 0) {
            return;
        }

        usleep($ms * 1000);
    }
}
