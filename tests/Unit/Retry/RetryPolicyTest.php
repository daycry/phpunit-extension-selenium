<?php

declare(strict_types=1);

namespace Daycry\PHPUnit\Selenium\Tests\Unit\Retry;

use Daycry\PHPUnit\Selenium\Config\RetryConfig;
use Daycry\PHPUnit\Selenium\Retry\Clock;
use Daycry\PHPUnit\Selenium\Retry\RetryPolicy;
use Facebook\WebDriver\Exception\NoSuchElementException;
use Facebook\WebDriver\Exception\StaleElementReferenceException;
use Facebook\WebDriver\Exception\TimeoutException;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class RetryPolicyTest extends TestCase
{
    public function testRetriesConfiguredExceptionsUntilMaxAttempts(): void
    {
        $config = new RetryConfig(maxAttempts: 3, initialDelayMs: 0, jitter: 0.0);
        $policy = new RetryPolicy($config, new FakeClock());

        self::assertTrue($policy->shouldRetry(new StaleElementReferenceException('x'), attempt: 1));
        self::assertTrue($policy->shouldRetry(new StaleElementReferenceException('x'), attempt: 2));
        self::assertFalse($policy->shouldRetry(new StaleElementReferenceException('x'), attempt: 3));
    }

    public function testDoesNotRetryUnrelatedExceptions(): void
    {
        $policy = new RetryPolicy(new RetryConfig(maxAttempts: 5, initialDelayMs: 0, jitter: 0.0), new FakeClock());

        self::assertFalse($policy->shouldRetry(new RuntimeException('boom'), attempt: 1));
    }

    public function testRetriesNoSuchElementByDefault(): void
    {
        $policy = new RetryPolicy(new RetryConfig(maxAttempts: 2, initialDelayMs: 0, jitter: 0.0), new FakeClock());

        self::assertTrue($policy->shouldRetry(new NoSuchElementException('x'), attempt: 1));
    }

    public function testCustomRetryableExceptionsAreHonoured(): void
    {
        $policy = new RetryPolicy(
            new RetryConfig(maxAttempts: 2, initialDelayMs: 0, jitter: 0.0, retryableExceptions: [TimeoutException::class]),
            new FakeClock(),
        );

        self::assertTrue($policy->shouldRetry(new TimeoutException('x'), attempt: 1));
        self::assertFalse($policy->shouldRetry(new StaleElementReferenceException('x'), attempt: 1));
    }

    public function testDelayGrowsExponentiallyAndCaps(): void
    {
        $config = new RetryConfig(
            maxAttempts: 5,
            initialDelayMs: 100,
            multiplier: 2.0,
            maxDelayMs: 500,
            jitter: 0.0,
        );
        $policy = new RetryPolicy($config, new FakeClock());

        self::assertSame(100, $policy->delayFor(1));
        self::assertSame(200, $policy->delayFor(2));
        self::assertSame(400, $policy->delayFor(3));
        self::assertSame(500, $policy->delayFor(4));
        self::assertSame(500, $policy->delayFor(5));
    }

    public function testSleepUsesInjectedClock(): void
    {
        $clock = new FakeClock();
        $policy = new RetryPolicy(new RetryConfig(initialDelayMs: 50, jitter: 0.0), $clock);

        $policy->sleep(123);

        self::assertSame([123], $clock->slept);
    }
}

final class FakeClock implements Clock
{
    /** @var list<int> */
    public array $slept = [];

    public function nowMs(): int
    {
        return 0;
    }

    public function sleepMs(int $ms): void
    {
        $this->slept[] = $ms;
    }
}
