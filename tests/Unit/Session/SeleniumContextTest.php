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
use Daycry\PHPUnit\Selenium\Session\SeleniumAware;
use Daycry\PHPUnit\Selenium\Session\SeleniumContext;
use Daycry\PHPUnit\Selenium\Session\SeleniumSession;
use Daycry\PHPUnit\Selenium\Session\SessionManager;
use Daycry\PHPUnit\Selenium\Session\WebDriverFactoryInterface;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use LogicException;
use PHPUnit\Framework\TestCase;

final class SeleniumContextTest extends TestCase
{
    protected function setUp(): void
    {
        SeleniumContext::reset();
    }

    protected function tearDown(): void
    {
        SeleniumContext::reset();
    }

    public function testCurrentThrowsWhenStackIsEmpty(): void
    {
        $this->expectException(SessionNotFoundException::class);
        SeleniumContext::current();
    }

    public function testPushPopRespectsStackOrder(): void
    {
        $a = $this->makeSession('a');
        $b = $this->makeSession('b');

        SeleniumContext::push($a);
        SeleniumContext::push($b);

        self::assertSame($b, SeleniumContext::current());
        self::assertSame($b, SeleniumContext::pop());
        self::assertSame($a, SeleniumContext::current());
    }

    public function testManagerBindAndAccess(): void
    {
        $manager = new SessionManager(new ContextFakeWebDriverFactory());
        SeleniumContext::bind($manager);

        self::assertSame($manager, SeleniumContext::manager());
    }

    public function testManagerThrowsWhenNotBound(): void
    {
        $this->expectException(SessionNotFoundException::class);
        SeleniumContext::manager();
    }

    public function testHasCurrentReflectsStackState(): void
    {
        self::assertFalse(SeleniumContext::hasCurrent());
        SeleniumContext::push($this->makeSession('a'));
        self::assertTrue(SeleniumContext::hasCurrent());
    }

    public function testSeleniumAwareTraitReturnsCurrentSession(): void
    {
        $session = $this->makeSession('aware');
        SeleniumContext::push($session);

        $consumer = new class () {
            use SeleniumAware;

            public function expose(): SeleniumSession
            {
                return $this->selenium();
            }
        };

        self::assertSame($session, $consumer->expose());
    }

    private function makeSession(string $id): SeleniumSession
    {
        return new SeleniumSession(
            $id,
            new ResolvedConfig(
                endpoint: new RemoteEndpoint(),
                browser: new BrowserConfig(),
                timeouts: new TimeoutConfig(),
                retry: new RetryConfig(),
                screenshot: new ScreenshotConfig(),
                reporting: new ReportingConfig(),
            ),
            new ContextFakeWebDriverFactory(),
        );
    }
}

final class ContextFakeWebDriverFactory implements WebDriverFactoryInterface
{
    public function create(ResolvedConfig $config): RemoteWebDriver
    {
        throw new LogicException('not invoked in these tests');
    }

    public function registry(): BrowserFactoryRegistry
    {
        return new BrowserFactoryRegistry();
    }
}
