<?php

declare(strict_types=1);

namespace Daycry\PHPUnit\Selenium\Tests\Unit\Config;

use Daycry\PHPUnit\Selenium\Config\TimeoutConfig;
use Daycry\PHPUnit\Selenium\Exception\ConfigurationException;
use PHPUnit\Framework\TestCase;

final class TimeoutConfigTest extends TestCase
{
    public function testDefaults(): void
    {
        $cfg = new TimeoutConfig();

        self::assertSame(0, $cfg->implicitWaitMs);
        self::assertSame(30_000, $cfg->pageLoadMs);
        self::assertSame(30_000, $cfg->scriptMs);
        self::assertSame(30_000, $cfg->defaultExplicitWaitMs);
        self::assertSame(250, $cfg->pollIntervalMs);
    }

    public function testNegativeImplicitWaitRejected(): void
    {
        $this->expectException(ConfigurationException::class);
        new TimeoutConfig(implicitWaitMs: -1);
    }

    public function testZeroPollIntervalRejected(): void
    {
        $this->expectException(ConfigurationException::class);
        new TimeoutConfig(pollIntervalMs: 0);
    }
}
