<?php

declare(strict_types=1);

namespace Daycry\PHPUnit\Selenium\Tests\Unit\Browser\Factory;

use Daycry\PHPUnit\Selenium\Browser\BrowserType;
use Daycry\PHPUnit\Selenium\Browser\Capabilities\ChromeCapabilities;
use Daycry\PHPUnit\Selenium\Browser\Capabilities\EdgeCapabilities;
use Daycry\PHPUnit\Selenium\Browser\Capabilities\FirefoxCapabilities;
use Daycry\PHPUnit\Selenium\Browser\Capabilities\SafariCapabilities;
use Daycry\PHPUnit\Selenium\Browser\Factory\ChromeDriverFactory;
use Daycry\PHPUnit\Selenium\Browser\Factory\EdgeDriverFactory;
use Daycry\PHPUnit\Selenium\Browser\Factory\FirefoxDriverFactory;
use Daycry\PHPUnit\Selenium\Browser\Factory\SafariDriverFactory;
use Daycry\PHPUnit\Selenium\Exception\ConfigurationException;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use PHPUnit\Framework\TestCase;

final class BrowserCapabilitiesBuildersTest extends TestCase
{
    public function testChromeFactoryBuildsW3CCapabilities(): void
    {
        $factory = new ChromeDriverFactory();
        $capabilities = $factory->buildCapabilities(new ChromeCapabilities(
            args: ['--headless'],
            browserVersion: '125',
            platformName: 'linux',
            acceptInsecureCerts: true,
            pageLoadStrategy: 'eager',
            userAgent: 'mock-agent',
        ));

        self::assertInstanceOf(DesiredCapabilities::class, $capabilities);
        self::assertSame('chrome', $capabilities->getBrowserName());
        self::assertSame('125', $capabilities->getCapability('browserVersion'));
        self::assertSame('linux', $capabilities->getCapability('platformName'));
        self::assertNotNull($capabilities->getCapability(ChromeOptions::CAPABILITY_W3C));
    }

    public function testFirefoxFactoryBuildsCapabilities(): void
    {
        $factory = new FirefoxDriverFactory();
        $capabilities = $factory->buildCapabilities(new FirefoxCapabilities(
            args: ['--headless'],
            prefs: ['browser.startup.homepage' => 'about:blank'],
            acceptInsecureCerts: true,
        ));

        self::assertSame('firefox', $capabilities->getBrowserName());
        self::assertTrue((bool) $capabilities->getCapability('acceptInsecureCerts'));
    }

    public function testEdgeFactoryUsesEdgeOptionsKey(): void
    {
        $factory = new EdgeDriverFactory();
        $capabilities = $factory->buildCapabilities(new EdgeCapabilities(
            args: ['--inprivate'],
            useChromium: true,
        ));

        self::assertSame('MicrosoftEdge', $capabilities->getBrowserName());
        self::assertIsArray($capabilities->getCapability('ms:edgeOptions'));
    }

    public function testSafariFactorySwitchesToTechnologyPreviewBrowserName(): void
    {
        $factory = new SafariDriverFactory();
        $capabilities = $factory->buildCapabilities(new SafariCapabilities(technologyPreview: true));

        self::assertSame('safari technology preview', $capabilities->getBrowserName());
    }

    public function testChromeFactoryRejectsForeignCapabilityType(): void
    {
        $factory = new ChromeDriverFactory();

        $this->expectException(ConfigurationException::class);
        $factory->buildCapabilities(new FirefoxCapabilities());
    }

    public function testFactorySupportsExpectedBrowsers(): void
    {
        self::assertTrue((new ChromeDriverFactory())->supports(BrowserType::Chrome));
        self::assertTrue((new FirefoxDriverFactory())->supports(BrowserType::Firefox));
        self::assertTrue((new EdgeDriverFactory())->supports(BrowserType::Edge));
        self::assertTrue((new SafariDriverFactory())->supports(BrowserType::Safari));

        self::assertFalse((new ChromeDriverFactory())->supports(BrowserType::Firefox));
    }
}
