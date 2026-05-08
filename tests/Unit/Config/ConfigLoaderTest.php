<?php

declare(strict_types=1);

namespace Daycry\PHPUnit\Selenium\Tests\Unit\Config;

use Daycry\PHPUnit\Selenium\Browser\BrowserType;
use Daycry\PHPUnit\Selenium\Browser\Capabilities\ChromeCapabilities;
use Daycry\PHPUnit\Selenium\Browser\Capabilities\FirefoxCapabilities;
use Daycry\PHPUnit\Selenium\Config\Loader\ArrayConfigSource;
use Daycry\PHPUnit\Selenium\Config\Loader\ConfigLoader;
use Daycry\PHPUnit\Selenium\Config\ScreenshotMode;
use PHPUnit\Framework\TestCase;

final class ConfigLoaderTest extends TestCase
{
    public function testEmptySourcesProduceDefaultConfig(): void
    {
        $config = (new ConfigLoader([]))->load();

        self::assertSame('http://localhost:4444/wd/hub', $config->endpoint->host);
        self::assertSame(BrowserType::Chrome, $config->browser->browser);
        self::assertInstanceOf(ChromeCapabilities::class, $config->browser->capabilities);
    }

    public function testHigherPrioritySourceOverridesLower(): void
    {
        $base = new ArrayConfigSource(['host' => 'http://low/wd/hub'], priority: 10);
        $env = new ArrayConfigSource(['host' => 'http://high/wd/hub'], priority: 100);

        $config = (new ConfigLoader([$base, $env]))->load();

        self::assertSame('http://high/wd/hub', $config->endpoint->host);
    }

    public function testFirefoxBrowserSelectsFirefoxCapabilities(): void
    {
        $config = (new ConfigLoader([
            new ArrayConfigSource(['browser-name' => 'firefox', 'options' => '--headless']),
        ]))->load();

        self::assertSame(BrowserType::Firefox, $config->browser->browser);
        self::assertInstanceOf(FirefoxCapabilities::class, $config->browser->capabilities);
        self::assertContains('--headless', $config->browser->capabilities->args);
    }

    public function testRetryAndTimeoutAreParsed(): void
    {
        $config = (new ConfigLoader([
            new ArrayConfigSource([
                'retry-max-attempts' => '4',
                'retry-initial-delay-ms' => '50',
                'retry-multiplier' => '1.5',
                'timeout-page-load-ms' => '15000',
                'timeout-explicit-ms' => '20000',
            ]),
        ]))->load();

        self::assertSame(4, $config->retry->maxAttempts);
        self::assertSame(50, $config->retry->initialDelayMs);
        self::assertEqualsWithDelta(1.5, $config->retry->multiplier, 0.0001);
        self::assertSame(15_000, $config->timeouts->pageLoadMs);
        self::assertSame(20_000, $config->timeouts->defaultExplicitWaitMs);
    }

    public function testScreenshotConfigParsing(): void
    {
        $config = (new ConfigLoader([
            new ArrayConfigSource([
                'screenshot' => 'true',
                'screenshot-mode' => 'every-step',
                'screenshot-format' => 'png',
                'screenshot-path' => sys_get_temp_dir() . '/selenium-test-' . uniqid(),
            ]),
        ]))->load();

        self::assertTrue($config->screenshot->enabled);
        self::assertSame(ScreenshotMode::EveryStep, $config->screenshot->mode);
    }

    public function testCommaSeparatedOptionsAreSplit(): void
    {
        $config = (new ConfigLoader([
            new ArrayConfigSource(['options' => '--a, --b,--c']),
        ]))->load();

        self::assertInstanceOf(ChromeCapabilities::class, $config->browser->capabilities);
        self::assertSame(['--a', '--b', '--c'], $config->browser->capabilities->args);
    }
}
