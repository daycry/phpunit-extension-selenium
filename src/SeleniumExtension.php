<?php

declare(strict_types=1);

namespace Daycry\PHPUnit\Selenium;

use Daycry\PHPUnit\Selenium\Subscribers\ConfigurationSubscriber;
use Daycry\PHPUnit\Selenium\Subscribers\FinishSeleniumSubscriber;
use Daycry\PHPUnit\Selenium\Subscribers\StartSeleniumSubscriber;
use PHPUnit\Runner\Extension\Extension;
use PHPUnit\TextUI\Configuration\Configuration;
use PHPUnit\Runner\Extension\Facade;
use PHPUnit\Runner\Extension\ParameterCollection;

class SeleniumExtension implements Extension
{
    public function bootstrap(Configuration $configuration, Facade $facade, ParameterCollection $parameters): void
    {
        try {
            $facade->registerSubscriber(
    new ConfigurationSubscriber(
                $this->parameter($parameters, 'host') ?? 'http://localhost:4444/wd/hub',
                $this->parameterAsArray($parameters, 'options') ?? ['--start-maximized', '--disable-infobars', '--disable-extensions'],
                $this->parameter($parameters, 'browser-name') ?? 'chrome',
                $this->parameter($parameters, 'platform') ?? 'linux',
                $this->parameterAsBool($parameters, 'accept-insecure-certs') ?? true,
                $this->parameterAsBool($parameters, 'screenshot') ?? false,
                $this->parameterAsBool($parameters, 'allure') ?? false,
                $this->parameter($parameters, 'browser-version') ?? null,
                $this->parameter($parameters, 'screenshot-path') ?? null,
                $this->parameter($parameters, 'page-load-strategy') ?? null,
                $this->parameter($parameters, 'user-agent') ?? null,
                )
            );
        
            $facade->registerSubscriber(new StartSeleniumSubscriber());
            $facade->registerSubscriber(new FinishSeleniumSubscriber());
        } catch (\Throwable $e) {
            echo "\n[SELENIUM EXTENSION] Failed to initialize ConfigurationSubscriber: {$e->getMessage()} : {$e->getTraceAsString()}\n";
            exit;
        }
    }

    private function parameter(ParameterCollection $parameters, string $name): ?string
    {
        if ($parameters->has($name)) {
            return $parameters->get($name);
        }

        return null;
    }

    private function parameterAsBool(ParameterCollection $parameters, string $name): bool
    {
        $value = $this->parameter($parameters, $name);

        if ($value === null) {
            return false;
        }

        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    /** @return array<string>|null */
    private function parameterAsArray(ParameterCollection $parameters, string $name): ?array
    {
        $value = $this->parameter($parameters, $name);

        if ($value === null || trim($value) === '') {
            return null;
        }

        return array_map(
            fn (string $value): string => trim($value),
            explode(",", $value)
        );
    }
}