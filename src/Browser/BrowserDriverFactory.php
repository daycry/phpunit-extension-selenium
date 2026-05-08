<?php

declare(strict_types=1);

namespace Daycry\PHPUnit\Selenium\Browser;

use Daycry\PHPUnit\Selenium\Browser\Capabilities\BrowserCapabilities;
use Daycry\PHPUnit\Selenium\Config\RemoteEndpoint;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;

interface BrowserDriverFactory
{
    public function supports(BrowserType $browser): bool;

    public function buildCapabilities(BrowserCapabilities $capabilities): DesiredCapabilities;

    public function create(BrowserCapabilities $capabilities, RemoteEndpoint $endpoint): RemoteWebDriver;
}
