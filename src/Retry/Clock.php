<?php

declare(strict_types=1);

namespace Daycry\PHPUnit\Selenium\Retry;

interface Clock
{
    public function nowMs(): int;

    public function sleepMs(int $ms): void;
}
