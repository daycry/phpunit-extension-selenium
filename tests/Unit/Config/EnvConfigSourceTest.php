<?php

declare(strict_types=1);

namespace Daycry\PHPUnit\Selenium\Tests\Unit\Config;

use Daycry\PHPUnit\Selenium\Config\Loader\EnvConfigSource;
use PHPUnit\Framework\TestCase;

final class EnvConfigSourceTest extends TestCase
{
    public function testReadsKnownVariablesAndIgnoresOthers(): void
    {
        $reader = static fn (string $name): string|false => match ($name) {
            'SELENIUM_HOST' => 'http://grid:4444/wd/hub',
            'SELENIUM_BROWSER' => 'firefox',
            'SELENIUM_RETRY_MAX_ATTEMPTS' => '5',
            'UNKNOWN_VAR' => 'ignore',
            default => false,
        };

        $values = (new EnvConfigSource($reader))->load();

        self::assertSame('http://grid:4444/wd/hub', $values['host']);
        self::assertSame('firefox', $values['browser-name']);
        self::assertSame('5', $values['retry-max-attempts']);
        self::assertArrayNotHasKey('UNKNOWN_VAR', $values);
    }

    public function testEmptyValuesAreIgnored(): void
    {
        $reader = static fn (string $name): string|false => $name === 'SELENIUM_HOST' ? '' : false;

        self::assertSame([], (new EnvConfigSource($reader))->load());
    }

    public function testPriorityIsHighEnoughToBeatXml(): void
    {
        self::assertGreaterThan(50, (new EnvConfigSource())->priority());
    }
}
