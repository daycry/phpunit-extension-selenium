<?php

declare(strict_types=1);

namespace Daycry\PHPUnit\Selenium\Subscribers;

use Daycry\PHPUnit\Selenium\Libraries\SeleniumDriver;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use PHPUnit\Event\TestRunner\ExecutionStarted;
use PHPUnit\Event\TestRunner\ExecutionStartedSubscriber;

/**
 * @codeCoverageIgnore
 */
class ConfigurationSubscriber implements ExecutionStartedSubscriber
{
    public function __construct(
        private readonly string $host,
        private readonly array $options,
        private readonly string $browserName,
        private readonly string $platformName,
        private readonly bool $acceptInsecureCerts = true,
        private readonly bool $screenshot = false,
        private readonly bool $allure = false,
        private readonly ?string $browserVersion = null,
        private readonly ?string $screenshotPath = null,
        private readonly ?string $pageLoadStrategy = null,
        private readonly ?string $userAgent = null,
    ) {
        // Validaciones básicas
        if (filter_var($this->host, FILTER_VALIDATE_URL) === false) {
            throw new \InvalidArgumentException('Host Selenium inválido: ' . $this->host);
        }

        if ($this->screenshot && $this->screenshotPath !== null) {
            if (trim($this->screenshotPath) === '') {
                throw new \InvalidArgumentException('Ruta de screenshot vacía.');
            }
        }
    }

    public function notify(ExecutionStarted $event): void
    {
        $options = new ChromeOptions();
        if ($this->options !== null) {
            foreach ($this->options as $option) {
                $options->addArguments([$option]);
            }
        }

        $capabilitiesArray = [];
        $capabilitiesArray[ChromeOptions::CAPABILITY_W3C] = $options;
        $capabilitiesArray['browserName'] = $this->browserName;
        if ($this->browserVersion !== null) {
            $capabilitiesArray['browserVersion'] = $this->browserVersion;
        }
        $capabilitiesArray['platformName'] = $this->platformName;
        $capabilitiesArray['acceptInsecureCerts'] = $this->acceptInsecureCerts;
        if ($this->pageLoadStrategy !== null) {
            $capabilitiesArray['pageLoadStrategy'] = $this->pageLoadStrategy;
        }
        if ($this->userAgent !== null) {
            $capabilitiesArray['userAgent'] = $this->userAgent;
        }

        $capabilities = new DesiredCapabilities($capabilitiesArray);

        SeleniumDriver::setHost($this->host);
        SeleniumDriver::setCapabilities($capabilities);

        if ($this->screenshot && $this->screenshotPath !== null) {
            SeleniumDriver::setScreenshotPath($this->screenshotPath);
        }
    }
}
