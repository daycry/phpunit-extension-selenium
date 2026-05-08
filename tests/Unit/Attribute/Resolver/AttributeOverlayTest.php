<?php

declare(strict_types=1);

namespace Daycry\PHPUnit\Selenium\Tests\Unit\Attribute\Resolver;

use Daycry\PHPUnit\Selenium\Attribute\Resolver\AttributeOverlay;
use Daycry\PHPUnit\Selenium\Attribute\Resolver\ResolvedAttributes;
use Daycry\PHPUnit\Selenium\Attributes\UseSelenium;
use Daycry\PHPUnit\Selenium\Browser\BrowserType;
use Daycry\PHPUnit\Selenium\Browser\Capabilities\FirefoxCapabilities;
use Daycry\PHPUnit\Selenium\Config\BrowserConfig;
use Daycry\PHPUnit\Selenium\Config\SeleniumConfig;
use PHPUnit\Framework\TestCase;

final class AttributeOverlayTest extends TestCase
{
    public function testNullAttributesReturnsBaseConfig(): void
    {
        $base = new SeleniumConfig();
        $resolved = (new AttributeOverlay())->apply($base, null);

        self::assertSame(BrowserType::Chrome, $resolved->browser->browser);
        self::assertSame($base->endpoint, $resolved->endpoint);
    }

    public function testBrowserOverrideSwitchesCapabilitiesType(): void
    {
        $overlay = new AttributeOverlay();

        $resolved = $overlay->apply(
            new SeleniumConfig(),
            ResolvedAttributes::merge([new UseSelenium(browser: 'firefox')]),
        );

        self::assertSame(BrowserType::Firefox, $resolved->browser->browser);
        self::assertInstanceOf(FirefoxCapabilities::class, $resolved->browser->capabilities);
    }

    public function testTimeoutSecondsOverridesExplicitWait(): void
    {
        $overlay = new AttributeOverlay();

        $resolved = $overlay->apply(
            new SeleniumConfig(),
            ResolvedAttributes::merge([new UseSelenium(timeoutSeconds: 60)]),
        );

        self::assertSame(60_000, $resolved->timeouts->defaultExplicitWaitMs);
    }

    public function testRetryAttemptsOverride(): void
    {
        $overlay = new AttributeOverlay();

        $resolved = $overlay->apply(
            new SeleniumConfig(),
            ResolvedAttributes::merge([new UseSelenium(retryAttempts: 5)]),
        );

        self::assertSame(5, $resolved->retry->maxAttempts);
    }

    public function testScreenshotOverride(): void
    {
        $overlay = new AttributeOverlay();

        $resolved = $overlay->apply(
            new SeleniumConfig(),
            ResolvedAttributes::merge([new UseSelenium(screenshot: true)]),
        );

        self::assertTrue($resolved->screenshot->enabled);
    }

    public function testProfileSelectsNamedConfig(): void
    {
        $mobileProfile = new SeleniumConfig(browser: BrowserConfig::firefox());
        $base = new SeleniumConfig(profiles: ['mobile' => $mobileProfile]);
        $overlay = new AttributeOverlay();

        $resolved = $overlay->apply($base, ResolvedAttributes::merge([new UseSelenium(profile: 'mobile')]));

        self::assertSame(BrowserType::Firefox, $resolved->browser->browser);
    }
}
