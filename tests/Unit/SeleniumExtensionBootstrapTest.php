<?php

declare(strict_types=1);

namespace Daycry\PHPUnit\Selenium\Tests\Unit;

use Daycry\PHPUnit\Selenium\Attribute\Resolver\TestAttributeResolver;
use Daycry\PHPUnit\Selenium\Config\Loader\ArrayConfigSource;
use Daycry\PHPUnit\Selenium\Config\SeleniumConfig;
use Daycry\PHPUnit\Selenium\Container\ContainerBuilder;
use Daycry\PHPUnit\Selenium\Exception\ConfigurationException;
use Daycry\PHPUnit\Selenium\Screenshot\ScreenshotService;
use Daycry\PHPUnit\Selenium\Session\SessionManager;
use Daycry\PHPUnit\Selenium\Session\WebDriverFactoryInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

final class SeleniumExtensionBootstrapTest extends TestCase
{
    public function testContainerWiresExpectedServices(): void
    {
        $container = (new ContainerBuilder())->build([]);

        self::assertInstanceOf(SessionManager::class, $container->get(SessionManager::class));
        self::assertInstanceOf(WebDriverFactoryInterface::class, $container->get(WebDriverFactoryInterface::class));
        self::assertInstanceOf(TestAttributeResolver::class, $container->get(TestAttributeResolver::class));
        self::assertInstanceOf(ScreenshotService::class, $container->get(ScreenshotService::class));
        self::assertInstanceOf(LoggerInterface::class, $container->get(LoggerInterface::class));
        self::assertInstanceOf(SeleniumConfig::class, $container->get(ContainerBuilder::CONFIG_SERVICE));
    }

    public function testContainerLazilyResolvesServices(): void
    {
        $container = (new ContainerBuilder())->build([]);

        $first = $container->get(SessionManager::class);
        $second = $container->get(SessionManager::class);

        self::assertSame($first, $second);
    }

    public function testInvalidHostInSourcesFailsConfigurationLoad(): void
    {
        $container = (new ContainerBuilder())->build([
            new ArrayConfigSource(['host' => 'not-a-url'], priority: 100),
        ]);

        $this->expectException(ConfigurationException::class);
        $container->get(ContainerBuilder::CONFIG_SERVICE);
    }
}
