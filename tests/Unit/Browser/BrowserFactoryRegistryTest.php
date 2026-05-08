<?php

declare(strict_types=1);

namespace Daycry\PHPUnit\Selenium\Tests\Unit\Browser;

use Daycry\PHPUnit\Selenium\Browser\BrowserFactoryRegistry;
use Daycry\PHPUnit\Selenium\Browser\BrowserType;
use Daycry\PHPUnit\Selenium\Browser\Factory\ChromeDriverFactory;
use Daycry\PHPUnit\Selenium\Browser\Factory\EdgeDriverFactory;
use Daycry\PHPUnit\Selenium\Browser\Factory\FirefoxDriverFactory;
use Daycry\PHPUnit\Selenium\Browser\Factory\SafariDriverFactory;
use PHPUnit\Framework\TestCase;

final class BrowserFactoryRegistryTest extends TestCase
{
    public function testRegistersDefaultFactoriesForAllBrowsers(): void
    {
        $registry = new BrowserFactoryRegistry();

        self::assertInstanceOf(ChromeDriverFactory::class, $registry->for(BrowserType::Chrome));
        self::assertInstanceOf(FirefoxDriverFactory::class, $registry->for(BrowserType::Firefox));
        self::assertInstanceOf(EdgeDriverFactory::class, $registry->for(BrowserType::Edge));
        self::assertInstanceOf(SafariDriverFactory::class, $registry->for(BrowserType::Safari));
    }
}
