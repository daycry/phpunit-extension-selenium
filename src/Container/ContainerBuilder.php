<?php

declare(strict_types=1);

namespace Daycry\PHPUnit\Selenium\Container;

use Daycry\PHPUnit\Selenium\Attribute\Resolver\TestAttributeResolver;
use Daycry\PHPUnit\Selenium\Config\Loader\ArrayConfigSource;
use Daycry\PHPUnit\Selenium\Config\Loader\ConfigLoader;
use Daycry\PHPUnit\Selenium\Config\Loader\ConfigSource;
use Daycry\PHPUnit\Selenium\Config\SeleniumConfig;
use Daycry\PHPUnit\Selenium\Reporting\AllureReporter;
use Daycry\PHPUnit\Selenium\Reporting\BrowserLogCollector;
use Daycry\PHPUnit\Selenium\Screenshot\ScreenshotService;
use Daycry\PHPUnit\Selenium\Session\DefaultWebDriverFactory;
use Daycry\PHPUnit\Selenium\Session\SessionManager;
use Daycry\PHPUnit\Selenium\Session\WebDriverFactoryInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Wires together the v2 service graph used by SeleniumExtension. Kept
 * standalone so it can be exercised in unit tests without instantiating
 * PHPUnit's Configuration.
 */
final class ContainerBuilder
{
    public const string CONFIG_SERVICE = 'selenium.config';

    /**
     * @param list<ConfigSource> $sources Highest priority first overrides lower; the
     *                                    builder appends a default-fallback source last.
     */
    public function build(array $sources): ServiceContainer
    {
        $container = new ServiceContainer();

        $allSources = [...$sources, $this->defaultsSource()];

        $container->set(self::CONFIG_SERVICE, static fn (): SeleniumConfig => (new ConfigLoader($allSources))->load());

        $container->set(LoggerInterface::class, static fn (): LoggerInterface => new NullLogger());

        $container->set(WebDriverFactoryInterface::class, static fn (): WebDriverFactoryInterface => new DefaultWebDriverFactory());

        $container->set(SessionManager::class, static fn (ServiceContainer $c): SessionManager => new SessionManager(
            $c->get(WebDriverFactoryInterface::class),
        ));

        $container->set(TestAttributeResolver::class, static fn (): TestAttributeResolver => new TestAttributeResolver());

        $container->set(ScreenshotService::class, static fn (ServiceContainer $c): ScreenshotService => new ScreenshotService(
            $c->get(self::CONFIG_SERVICE)->screenshot,
        ));

        $container->set(AllureReporter::class, static fn (ServiceContainer $c): AllureReporter => new AllureReporter(
            $c->get(self::CONFIG_SERVICE)->reporting,
        ));

        $container->set(BrowserLogCollector::class, static fn (): BrowserLogCollector => new BrowserLogCollector());

        return $container;
    }

    private function defaultsSource(): ConfigSource
    {
        return new ArrayConfigSource(
            values: [
                'host' => 'http://localhost:4444/wd/hub',
                'browser-name' => 'chrome',
                'platform-name' => 'linux',
                'accept-insecure-certs' => 'true',
                'options' => '--start-maximized,--disable-infobars,--disable-extensions',
            ],
            priority: 0,
        );
    }
}
