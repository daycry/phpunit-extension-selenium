<?php

declare(strict_types=1);

namespace Daycry\PHPUnit\Selenium\Traits;

use Daycry\PHPUnit\Selenium\Subscribers\ConfigurationSubscriber;
use Facebook\WebDriver\Exception\WebDriverException;

trait SeleniumActions
{
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
    }

    protected function takeScreenshot(string $filename): void
    {
        try {
            if(ConfigurationSubscriber::getInstance() === null) {
                throw new WebDriverException('WebDriver instance is not available.');
            }
            $screenshotDir = WRITEPATH . 'tests/screenshots/';
            if (! is_dir($screenshotDir)) {
                mkdir($screenshotDir, 0777, true);
            }
            $this->driver->takeScreenshot($screenshotDir . $filename);
        } catch (WebDriverException $se) {
            echo "\n[SCREENSHOT] Failed to save screenshot: {$se->getMessage()}\n";
        }
    }
}