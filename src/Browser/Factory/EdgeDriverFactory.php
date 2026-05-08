<?php

declare(strict_types=1);

namespace Daycry\PHPUnit\Selenium\Browser\Factory;

use Daycry\PHPUnit\Selenium\Browser\BrowserDriverFactory;
use Daycry\PHPUnit\Selenium\Browser\BrowserType;
use Daycry\PHPUnit\Selenium\Browser\Capabilities\BrowserCapabilities;
use Daycry\PHPUnit\Selenium\Browser\Capabilities\EdgeCapabilities;
use Daycry\PHPUnit\Selenium\Config\RemoteEndpoint;
use Daycry\PHPUnit\Selenium\Exception\ConfigurationException;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;

final class EdgeDriverFactory implements BrowserDriverFactory
{
    public function supports(BrowserType $browser): bool
    {
        return $browser === BrowserType::Edge;
    }

    public function buildCapabilities(BrowserCapabilities $capabilities): DesiredCapabilities
    {
        if (! $capabilities instanceof EdgeCapabilities) {
            throw new ConfigurationException(\sprintf(
                'EdgeDriverFactory requires EdgeCapabilities, got %s.',
                $capabilities::class,
            ));
        }

        $edgeOptions = [
            'args' => $capabilities->args,
            'useChromium' => $capabilities->useChromium,
        ];

        if ($capabilities->prefs !== []) {
            $edgeOptions['prefs'] = $capabilities->prefs;
        }

        $array = [
            'browserName' => 'MicrosoftEdge',
            'acceptInsecureCerts' => $capabilities->acceptInsecureCerts,
            'ms:edgeOptions' => $edgeOptions,
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
