<?php

declare(strict_types=1);

namespace Daycry\PHPUnit\Selenium\Tests\Unit\Config;

use Daycry\PHPUnit\Selenium\Config\RetryConfig;
use Daycry\PHPUnit\Selenium\Exception\ConfigurationException;
use Facebook\WebDriver\Exception\StaleElementReferenceException;
use PHPUnit\Framework\TestCase;

final class RetryConfigTest extends TestCase
{
    public function testDefaultsIncludeFlakyExceptions(): void
    {
        $cfg = new RetryConfig();

        self::assertSame(1, $cfg->maxAttempts);
        self::assertContains(StaleElementReferenceException::class, $cfg->retryableExceptions);
    }

    public function testZeroAttemptsRejected(): void
    {
        $this->expectException(ConfigurationException::class);
        new RetryConfig(maxAttempts: 0);
    }

    public function testNegativeInitialDelayRejected(): void
    {
        $this->expectException(ConfigurationException::class);
        new RetryConfig(initialDelayMs: -1);
    }

    public function testMultiplierLessThanOneRejected(): void
    {
        $this->expectException(ConfigurationException::class);
        new RetryConfig(multiplier: 0.5);
    }

    public function testMaxDelayBelowInitialRejected(): void
    {
        $this->expectException(ConfigurationException::class);
        new RetryConfig(initialDelayMs: 1000, maxDelayMs: 500);
    }

    public function testJitterOutOfRangeRejected(): void
    {
        $this->expectException(ConfigurationException::class);
        new RetryConfig(jitter: 1.5);
    }
}
