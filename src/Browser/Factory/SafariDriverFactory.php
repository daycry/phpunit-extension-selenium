<?php

declare(strict_types=1);

namespace Daycry\PHPUnit\Selenium\Browser\Factory;

use Daycry\PHPUnit\Selenium\Browser\BrowserDriverFactory;
use Daycry\PHPUnit\Selenium\Browser\BrowserType;
use Daycry\PHPUnit\Selenium\Browser\Capabilities\BrowserCapabilities;
use Daycry\PHPUnit\Selenium\Browser\Capabilities\SafariCapabilities;
use Daycry\PHPUnit\Selenium\Config\RemoteEndpoint;
use Daycry\PHPUnit\Selenium\Exception\ConfigurationException;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;

final class SafariDriverFactory implements BrowserDriverFactory
{
    public function supports(BrowserType $browser): bool
    {
        return $browser === BrowserType::Safari;
    }

    public function buildCapabilities(BrowserCapabilities $capabilities): DesiredCapabilities
    {
        if (! $capabilities instanceof SafariCapabilities) {
            throw new ConfigurationException(\sprintf(
                'SafariDriverFactory requires SafariCapabilities, got %s.',
                $capabilities::class,
            ));
        }

        $array = [
            'browserName' => $capabilities->technologyPreview ? 'safari technology preview' : BrowserType::Safari->value,
            'acceptInsecureCerts' => $capabilities->acceptInsecureCerts,
        ];

        if ($capabilities->browserVersion !== null) {
            $array['browserVersion'] = $capabilities->browserVersion;
        }

        if ($capabilities->platformName !== null) {
            $array['platformName'] = $capabilities->platformName;
        }

        if ($capabilities->pageLoadStrategy !== null) {
            $array['pageLoadStrategy'] = $capabilities->pageLoadStrategy;
        }

        if ($capabilities->userAgent !== null) {
            $array['userAgent'] = $capabilities->userAgent;
        }

        foreach ($capabilities->extra as $key => $value) {
            $array[$key] = $value;
        }

        return new DesiredCapabilities($array);
    }

    public function create(BrowserCapabilities $capabilities, RemoteEndpoint $endpoint): RemoteWebDriver
    {
        return RemoteWebDriver::create(
            $endpoint->host,
            $this->buildCapabilities($capabilities),
            $endpoint->connectTimeoutMs,
            $endpoint->requestTimeoutMs,
        );
    }
}
