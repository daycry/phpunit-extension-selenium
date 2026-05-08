<?php

declare(strict_types=1);

namespace Daycry\PHPUnit\Selenium\Tests\Unit\Action;

use Daycry\PHPUnit\Selenium\Action\RetryingRunner;
use Daycry\PHPUnit\Selenium\Action\WebDriverRunner;
use Daycry\PHPUnit\Selenium\Config\RetryConfig;
use Daycry\PHPUnit\Selenium\Retry\Clock;
use Daycry\PHPUnit\Selenium\Retry\RetryPolicy;
use Daycry\PHPUnit\Selenium\Tests\Unit\Retry\FakeClock;
use Facebook\WebDriver\Exception\StaleElementReferenceException;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class RetryingRunnerTest extends TestCase
{
    public function testReturnsResultWhenInnerSucceeds(): void
    {
        $runner = new RetryingRunner(
            new WebDriverRunner(),
            new RetryPolicy(new RetryConfig(maxAttempts: 3, initialDelayMs: 0, jitter: 0.0), $this->clock()),
        );

        $result = $runner->run('noop', static fn (): string => 'ok');

        self::assertSame('ok', $result);
    }

    public function testRetriesOnRetryableExceptionAndEventuallySucceeds(): void
    {
        $attempts = 0;
        $runner = new RetryingRunner(
            new WebDriverRunner(),
            new RetryPolicy(new RetryConfig(maxAttempts: 3, initialDelayMs: 0, jitter: 0.0), $this->clock()),
        );

        $result = $runner->run('flaky', static function () use (&$attempts): string {
            ++$attempts;
            if ($attempts < 3) {
                throw new StaleElementReferenceException('flaky');
            }

            return 'eventually-ok';
        });

        self::assertSame('eventually-ok', $result);
        self::assertSame(3, $attempts);
    }

    public function testGivesUpAfterMaxAttempts(): void
    {
        $attempts = 0;
        $runner = new RetryingRunner(
            new WebDriverRunner(),
            new RetryPolicy(new RetryConfig(maxAttempts: 2, initialDelayMs: 0, jitter: 0.0), $this->clock()),
        );

        $this->expectException(StaleElementReferenceException::class);

        try {
            $runner->run('always-fails', static function () use (&$attempts): never {
                ++$attempts;
                throw new StaleElementReferenceException('boom');
            });
        } finally {
            self::assertSame(2, $attempts);
        }
    }

    public function testNonRetryableExceptionPropagatesImmediately(): void
    {
        $runner = new RetryingRunner(
            new WebDriverRunner(),
            new RetryPolicy(new RetryConfig(maxAttempts: 5, initialDelayMs: 0, jitter: 0.0), $this->clock()),
        );

        $this->expectException(RuntimeException::class);

        $runner->run('non-retryable', static function (): never {
            throw new RuntimeException('nope');
        });
    }

    private function clock(): Clock
    {
        return new FakeClock();
    }
}
