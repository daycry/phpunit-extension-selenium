<?php

declare(strict_types=1);

namespace Daycry\PHPUnit\Selenium\Libraries;

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverCapabilities;

class SeleniumDriver
{
    private static ?string $host = null;
    private static ?WebDriverCapabilities $capabilities = null;
    private static ?RemoteWebDriver $driver = null;
    private static ?string $screenshotPath = null;

    public static function setHost(string $host): void
    {
        self::$host = $host;
    }

    public static function setCapabilities(WebDriverCapabilities $capabilities): void
    {
        self::$capabilities = $capabilities;
    }

    public static function setScreenshotPath(string $path): void
    {
        self::$screenshotPath = $path;
    }

    public static function getScreenshotPath(): ?string
    {
        return self::$screenshotPath;
    }

    public static function initialize(): void
    {
        if (self::$driver === null) {
            try {
                self::$driver = RemoteWebDriver::create(self::$host, self::$capabilities);
            } catch (\Throwable $e) {
                echo "\n[SELENIUM DRIVER] Failed to initialize: {$e->getMessage()}\n";
            }
        }
    }

    public static function getDriver(bool $exception = true): ?RemoteWebDriver
    {
        if (!self::$driver) {
            if ($exception) {
                throw new \RuntimeException('Selenium WebDriver not initialized');
            }

            return null;
        }

        return self::$driver;
    }

    public static function quit(): void
    {
        if (self::$driver !== null) {
            self::$driver->quit();
            self::$driver = null;
        }
    }
}
