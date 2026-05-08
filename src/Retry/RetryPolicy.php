<?php

declare(strict_types=1);

namespace Daycry\PHPUnit\Selenium\Retry;

use Daycry\PHPUnit\Selenium\Config\RetryConfig;
use Throwable;

final readonly class RetryPolicy
{
    public function __construct(
        public RetryConfig $config,
        private Clock $clock = new SystemClock(),
    ) {
    }

    public function shouldRetry(Throwable $throwable, int $attempt): bool
    {
        if ($attempt >= $this->config->maxAttempts) {
            return false;
        }

        foreach ($this->config->retryableExceptions as $class) {
            if ($throwable instanceof $class) {
                return true;
            }
        }

        return false;
    }

    public function delayFor(int $attempt): int
    {
        $base = (int) round($this->config->initialDelayMs * ($this->config->multiplier ** max(0, $attempt - 1)));
        $capped = min($base, $this->config->maxDelayMs);

        if ($this->config->jitter <= 0.0) {
            return $capped;
        }

        $jitterRange = (int) round($capped * $this->config->jitter);
        if ($jitterRange <= 0) {
            return $capped;
        }

        return $capped + random_int(-$jitterRange, $jitterRange);
    }

    public function sleep(int $delayMs): void
    {
        $this->clock->sleepMs(max(0, $delayMs));
    }
}
