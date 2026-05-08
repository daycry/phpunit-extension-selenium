<?php

declare(strict_types=1);

namespace Daycry\PHPUnit\Selenium\Tests\Unit\Session;

use Daycry\PHPUnit\Selenium\Browser\BrowserFactoryRegistry;
use Daycry\PHPUnit\Selenium\Config\BrowserConfig;
use Daycry\PHPUnit\Selenium\Config\RemoteEndpoint;
use Daycry\PHPUnit\Selenium\Config\ReportingConfig;
use Daycry\PHPUnit\Selenium\Config\ResolvedConfig;
use Daycry\PHPUnit\Selenium\Config\RetryConfig;
use Daycry\PHPUnit\Selenium\Config\ScreenshotConfig;
use Daycry\PHPUnit\Selenium\Config\TimeoutConfig;
use Daycry\PHPUnit\Selenium\Exception\SessionNotFoundException;
use Daycry\PHPUnit\Selenium\Session\SessionManager;
use Daycry\PHPUnit\Selenium\Session\WebDriverFactoryInterface;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use LogicException;
use PHPUnit\Framework\TestCase;

final class SessionManagerTest extends TestCase
{
    public function testStartReturnsSessionAndIsIdempotent(): void
    {
        $manager = new SessionManager(new FakeWebDriverFactory());
        $config = $this->buildResolvedConfig();

        $first = $manager->start('TestA::testFoo', $config);
        $second = $manager->start('TestA::testFoo', $config);

        self::assertSame($first, $second);
        self::assertTrue($manager->has('TestA::testFoo'));
    }

    public function testGetUnknownSessionThrows(): void
    {
        $manager = new SessionManager(new FakeWebDriverFactory());

        $this->expectException(SessionNotFoundException::class);
        $manager->get('missing');
    }

    public function testCloseRemovesSession(): void
    {
        $manager = new SessionManager(new FakeWebDriverFactory());
        $manager->start('TestA::testFoo', $this->buildResolvedConfig());
        self::assertTrue($manager->has('TestA::testFoo'));

        $manager->close('TestA::testFoo');

        self::assertFalse($manager->has('TestA::testFoo'));
    }

    public function testCloseAllRemovesEveryRegisteredSession(): void
    {
        $manager = new SessionManager(new FakeWebDriverFactory());
        $manager->start('a', $this->buildResolvedConfig());
        $manager->start('b', $this->buildResolvedConfig());

        $manager->closeAll();

        self::assertSame([], $manager->all());
    }

    private function buildResolvedConfig(): ResolvedConfig
    {
        return new ResolvedConfig(
            endpoint: new RemoteEndpoint(),
            browser: new BrowserConfig(),
            timeouts: new TimeoutConfig(),
            retry: new RetryConfig(),
            screenshot: new ScreenshotConfig(),
            reporting: new ReportingConfig(),
        );
    }
}

final class FakeWebDriverFactory implements WebDriverFactoryInterface
{
    public function create(ResolvedConfig $config): RemoteWebDriver
    {
        // Sessions never start in these tests, so no need to return a real driver.
        // SessionManager::start() returns the session before the driver is created.
        throw new LogicException('FakeWebDriverFactory::create must not be invoked.');
    }

    public function registry(): BrowserFactoryRegistry
    {
        return new BrowserFactoryRegistry();
    }
}
