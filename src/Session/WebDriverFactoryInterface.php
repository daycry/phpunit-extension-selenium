<?php

declare(strict_types=1);

namespace Daycry\PHPUnit\Selenium\Session;

use Daycry\PHPUnit\Selenium\Browser\BrowserFactoryRegistry;
use Daycry\PHPUnit\Selenium\Config\ResolvedConfig;
use Facebook\WebDriver\Remote\RemoteWebDriver;

interface WebDriverFactoryInterface
{
    public function create(ResolvedConfig $config): RemoteWebDriver;

    public function registry(): BrowserFactoryRegistry;
}
