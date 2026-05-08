<?php

declare(strict_types=1);

namespace Daycry\PHPUnit\Selenium\Config;

use Daycry\PHPUnit\Selenium\Browser\BrowserType;
use Daycry\PHPUnit\Selenium\Browser\Capabilities\BrowserCapabilities;
use Daycry\PHPUnit\Selenium\Browser\Capabilities\ChromeCapabilities;
use Daycry\PHPUnit\Selenium\Browser\Capabilities\EdgeCapabilities;
use Daycry\PHPUnit\Selenium\Browser\Capabilities\FirefoxCapabilities;
use Daycry\PHPUnit\Selenium\Browser\Capabilities\SafariCapabilities;

final readonly class BrowserConfig
{
    public function __construct(
        public BrowserType $browser = BrowserType::Chrome,
        public BrowserCapabilities $capabilities = new ChromeCapabilities(),
    ) {
    }

    public static function chrome(?ChromeCapabilities $capabilities = null): self
    {
        return new self(BrowserType::Chrome, $capabilities ?? new ChromeCapabilities());
    }

    public static function firefox(?FirefoxCapabilities $capabilities = null): self
    {
        return new self(BrowserType::Firefox, $capabilities ?? new FirefoxCapabilities());
    }

    public static function edge(?EdgeCapabilities $capabilities = null): self
    {
        return new self(BrowserType::Edge, $capabilities ?? new EdgeCapabilities());
    }

    public static function safari(?SafariCapabilities $capabilities = null): self
    {
        return new self(BrowserType::Safari, $capabilities ?? new SafariCapabilities());
    }
}
