<?php

declare(strict_types=1);

namespace Daycry\PHPUnit\Selenium\Browser;

use Daycry\PHPUnit\Selenium\Browser\Factory\ChromeDriverFactory;
use Daycry\PHPUnit\Selenium\Browser\Factory\EdgeDriverFactory;
use Daycry\PHPUnit\Selenium\Browser\Factory\FirefoxDriverFactory;
use Daycry\PHPUnit\Selenium\Browser\Factory\SafariDriverFactory;
use Daycry\PHPUnit\Selenium\Exception\ConfigurationException;

final class BrowserFactoryRegistry
{
    /** @var list<BrowserDriverFactory> */
    private array $factories = [];

    public function __construct()
    {
        $this->register(new ChromeDriverFactory());
        $this->register(new FirefoxDriverFactory());
        $this->register(new EdgeDriverFactory());
        $this->register(new SafariDriverFactory());
    }

    public function register(BrowserDriverFactory $factory): void
    {
        $this->factories[] = $factory;
    }

    public function for(BrowserType $browser): BrowserDriverFactory
    {
        foreach ($this->factories as $factory) {
            if ($factory->supports($browser)) {
                return $factory;
            }
        }

        throw new ConfigurationException(\sprintf('No driver factory registered for browser "%s".', $browser->value));
    }
}
