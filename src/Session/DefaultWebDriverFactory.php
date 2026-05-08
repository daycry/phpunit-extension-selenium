<?php

declare(strict_types=1);

namespace Daycry\PHPUnit\Selenium\Session;

use Daycry\PHPUnit\Selenium\Browser\BrowserFactoryRegistry;
use Daycry\PHPUnit\Selenium\Config\ResolvedConfig;
use Facebook\WebDriver\Remote\RemoteWebDriver;

final readonly class DefaultWebDriverFactory implements WebDriverFactoryInterface
{
    public function __construct(private BrowserFactoryRegistry $registry = new BrowserFactoryRegistry())
    {
    }

    public function registry(): BrowserFactoryRegistry
    {
        return $this->registry;
    }

    public function create(ResolvedConfig $config): RemoteWebDriver
    {
        $factory = $this->registry->for($config->browser->browser);

        return $factory->create($config->browser->capabilities, $config->endpoint);
    }
}
