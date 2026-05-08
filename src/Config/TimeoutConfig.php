<?php

declare(strict_types=1);

namespace Daycry\PHPUnit\Selenium\Config;

use Daycry\PHPUnit\Selenium\Exception\ConfigurationException;

final readonly class TimeoutConfig
{
    public const DEFAULT_PAGE_LOAD_MS = 30_000;
    public const DEFAULT_SCRIPT_MS = 30_000;
    public const DEFAULT_IMPLICIT_WAIT_MS = 0;
    public const DEFAULT_EXPLICIT_WAIT_MS = 30_000;
    public const DEFAULT_POLL_INTERVAL_MS = 250;

    public function __construct(
        public int $implicitWaitMs = self::DEFAULT_IMPLICIT_WAIT_MS,
        public int $pageLoadMs = self::DEFAULT_PAGE_LOAD_MS,
        public int $scriptMs = self::DEFAULT_SCRIPT_MS,
        public int $defaultExplicitWaitMs = self::DEFAULT_EXPLICIT_WAIT_MS,
        public int $pollIntervalMs = self::DEFAULT_POLL_INTERVAL_MS,
    ) {
        $this->assertPositive('implicitWaitMs', $this->implicitWaitMs);
        $this->assertPositive('pageLoadMs', $this->pageLoadMs);
        $this->assertPositive('scriptMs', $this->scriptMs);
        $this->assertPositive('defaultExplicitWaitMs', $this->defaultExplicitWaitMs);

        if ($this->pollIntervalMs < 1) {
            throw new ConfigurationException(\sprintf('pollIntervalMs must be >= 1, got %d.', $this->pollIntervalMs));
        }
    }

    private function assertPositive(string $name, int $value): void
    {
        if ($value < 0) {
            throw new ConfigurationException(\sprintf('%s must be >= 0, got %d.', $name, $value));
        }
    }
}
