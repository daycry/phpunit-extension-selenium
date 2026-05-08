<?php

declare(strict_types=1);

namespace Daycry\PHPUnit\Selenium\Tests\Unit\Wait;

use Daycry\PHPUnit\Selenium\Locator\Locator;
use Daycry\PHPUnit\Selenium\Locator\LocatorResolver;
use Daycry\PHPUnit\Selenium\Wait\WaitBuilder;
use Daycry\PHPUnit\Selenium\Wait\WaitTimeoutException;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use PHPUnit\Framework\TestCase;

final class WaitBuilderTest extends TestCase
{
    public function testRunWithoutConditionThrows(): void
    {
        $builder = new WaitBuilder(
            driver: $this->driverStub(),
            resolver: new LocatorResolver(),
            timeoutMs: 50,
            pollIntervalMs: 10,
        );

        $this->expectException(WaitTimeoutException::class);
        $this->expectExceptionMessage('No wait condition was configured');

        $builder->run();
    }

    public function testTimeoutModifierReturnsNewInstance(): void
    {
        $builder = new WaitBuilder(
            driver: $this->driverStub(),
            resolver: new LocatorResolver(),
            timeoutMs: 100,
            pollIntervalMs: 10,
        );

        $derived = $builder->timeout(5);

        self::assertNotSame($builder, $derived);
    }

    public function testForElementBuilderConfiguresMessage(): void
    {
        $builder = new WaitBuilder(
            driver: $this->driverStub(),
            resolver: new LocatorResolver(),
            timeoutMs: 50,
            pollIntervalMs: 10,
        );

        $configured = $builder->forElement(Locator::id('foo'));

        self::assertNotSame($builder, $configured);
    }

    public function testCustomFunctionConditionEvaluatesAndStops(): void
    {
        $invocations = 0;
        $callback = static function (RemoteWebDriver $d) use (&$invocations): bool {
            ++$invocations;

            return $invocations >= 2;
        };

        $builder = (new WaitBuilder(
            driver: $this->driverStub(),
            resolver: new LocatorResolver(),
            timeoutMs: 1000,
            pollIntervalMs: 5,
        ))->forFunction($callback);

        self::assertTrue($builder->run());
        self::assertGreaterThanOrEqual(2, $invocations);
    }

    public function testCustomFunctionTimingOut(): void
    {
        $callback = static fn (RemoteWebDriver $d): bool => false;

        $builder = (new WaitBuilder(
            driver: $this->driverStub(),
            resolver: new LocatorResolver(),
            timeoutMs: 30,
            pollIntervalMs: 5,
        ))
            ->forFunction($callback)
            ->withMessage('never satisfies');

        $this->expectException(WaitTimeoutException::class);
        $this->expectExceptionMessage('never satisfies');

        $builder->run();
    }

    private function driverStub(): RemoteWebDriver
    {
        return $this->createStub(RemoteWebDriver::class);
    }
}
