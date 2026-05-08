<?php

declare(strict_types=1);

namespace Daycry\PHPUnit\Selenium;

use Daycry\PHPUnit\Selenium\Attribute\Resolver\TestAttributeResolver;
use Daycry\PHPUnit\Selenium\Config\Loader\EnvConfigSource;
use Daycry\PHPUnit\Selenium\Config\Loader\XmlConfigSource;
use Daycry\PHPUnit\Selenium\Config\SeleniumConfig;
use Daycry\PHPUnit\Selenium\Container\ContainerBuilder;
use Daycry\PHPUnit\Selenium\Exception\ExtensionBootstrapException;
use Daycry\PHPUnit\Selenium\Reporting\AllureReporter;
use Daycry\PHPUnit\Selenium\Reporting\BrowserLogCollector;
use Daycry\PHPUnit\Selenium\Screenshot\ScreenshotService;
use Daycry\PHPUnit\Selenium\Session\SessionManager;
use Daycry\PHPUnit\Selenium\Subscriber\BootstrapSubscriber;
use Daycry\PHPUnit\Selenium\Subscriber\FailedTestSubscriber;
use Daycry\PHPUnit\Selenium\Subscriber\FinishTestSubscriber;
use Daycry\PHPUnit\Selenium\Subscriber\ShutdownSubscriber;
use Daycry\PHPUnit\Selenium\Subscriber\StartTestSubscriber;
use PHPUnit\Runner\Extension\Extension;
use PHPUnit\Runner\Extension\Facade;
use PHPUnit\Runner\Extension\ParameterCollection;
use PHPUnit\TextUI\Configuration\Configuration;
use Psr\Log\LoggerInterface;
use Throwable;

final class SeleniumExtension implements Extension
{
    public function bootstrap(Configuration $configuration, Facade $facade, ParameterCollection $parameters): void
    {
        try {
            $container = (new ContainerBuilder())->build([
                new XmlConfigSource($parameters),
                new EnvConfigSource(),
            ]);

            /** @var SessionManager $sessions */
            $sessions = $container->get(SessionManager::class);
            /** @var SeleniumConfig $config */
            $config = $container->get(ContainerBuilder::CONFIG_SERVICE);
            /** @var TestAttributeResolver $resolver */
            $resolver = $container->get(TestAttributeResolver::class);
            /** @var ScreenshotService $screenshots */
            $screenshots = $container->get(ScreenshotService::class);
            /** @var LoggerInterface $logger */
            $logger = $container->get(LoggerInterface::class);
            /** @var AllureReporter $allure */
            $allure = $container->get(AllureReporter::class);
            /** @var BrowserLogCollector $logs */
            $logs = $container->get(BrowserLogCollector::class);

            $facade->registerSubscriber(new BootstrapSubscriber($config, $sessions, $logger));
            $facade->registerSubscriber(new StartTestSubscriber($config, $sessions, $resolver, logger: $logger));
            $facade->registerSubscriber(new FailedTestSubscriber($sessions, $screenshots, $logger, $allure, $logs));
            $facade->registerSubscriber(new FinishTestSubscriber($sessions, $logger));
            $facade->registerSubscriber(new ShutdownSubscriber($sessions, $logger));
        } catch (Throwable $e) {
            throw new ExtensionBootstrapException(
                \sprintf('[Selenium] Failed to bootstrap extension: %s', $e->getMessage()),
                0,
                $e,
            );
        }
    }
}
