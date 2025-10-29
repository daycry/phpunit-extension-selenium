<?php

declare(strict_types=1);

namespace Daycry\PHPUnit\Selenium\Subscribers;

use PHPUnit\Event\TestRunner\ExecutionStarted;
use PHPUnit\Event\TestRunner\ExecutionStartedSubscriber;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;

/**
 * @codeCoverageIgnore
 */
class ConfigurationSubscriber implements ExecutionStartedSubscriber
{
    /** @var RemoteWebDriver|null */
    private static $driver;

    public function __construct(
        private readonly string $host,
        private readonly array $options,
        private readonly string $browserName,
        private readonly string $platform,
        private readonly bool $acceptInsecureCerts = true,
        private readonly bool $screenshot = false,
        private readonly bool $allure = false,
        private readonly ?string $browserVersion = null,
        private readonly ?string $screenshotPath = null,
        private readonly ?string $pageLoadStrategy = null,
        private readonly ?string $userAgent = null,
    ) {
    }

    public function notify(ExecutionStarted $event): void
    {
        $options = new ChromeOptions();
        if ($this->options !== null) {
            foreach ($this->options as $option) {
                $options->addArguments([$option]);
            }
        }

        $capabilities = DesiredCapabilities::chrome();
        $capabilities->setCapability(ChromeOptions::CAPABILITY_W3C, $options);
        $capabilities->setCapability('browserName', $this->browserName);
        $capabilities->setCapability('browserVersion', $this->browserVersion);
        $capabilities->setCapability('platform', $this->platform);
        $capabilities->setCapability('acceptInsecureCerts', $this->acceptInsecureCerts);
        $capabilities->setCapability('pageLoadStrategy', $this->pageLoadStrategy);
        if ($this->userAgent !== null) {
            $capabilities->setCapability('userAgent', $this->userAgent);
        }

        self::$driver = RemoteWebDriver::create($this->host, $capabilities);
    }

    public static function getDriver(bool $exception = true): ?RemoteWebDriver
    {
        if (!self::$driver) {
            if($exception) {
                throw new \RuntimeException('Selenium WebDriver not initialized');
            }

            return null;
        }

        return self::$driver;
    }

    public static function quitDriver(): void
    {
        if (self::$driver !== null) {
            self::$driver->quit();
            self::$driver = null;
        }
    }
}
