<?php

declare(strict_types=1);

namespace Daycry\PHPUnit\Selenium\Config\Loader;

use Daycry\PHPUnit\Selenium\Browser\BrowserType;
use Daycry\PHPUnit\Selenium\Browser\Capabilities\BrowserCapabilities;
use Daycry\PHPUnit\Selenium\Browser\Capabilities\ChromeCapabilities;
use Daycry\PHPUnit\Selenium\Browser\Capabilities\EdgeCapabilities;
use Daycry\PHPUnit\Selenium\Browser\Capabilities\FirefoxCapabilities;
use Daycry\PHPUnit\Selenium\Browser\Capabilities\SafariCapabilities;
use Daycry\PHPUnit\Selenium\Config\BrowserConfig;
use Daycry\PHPUnit\Selenium\Config\RemoteEndpoint;
use Daycry\PHPUnit\Selenium\Config\ReportingConfig;
use Daycry\PHPUnit\Selenium\Config\RetryConfig;
use Daycry\PHPUnit\Selenium\Config\ScreenshotConfig;
use Daycry\PHPUnit\Selenium\Config\ScreenshotFormat;
use Daycry\PHPUnit\Selenium\Config\ScreenshotMode;
use Daycry\PHPUnit\Selenium\Config\SeleniumConfig;
use Daycry\PHPUnit\Selenium\Config\TimeoutConfig;

final readonly class ConfigLoader
{
    /**
     * @param iterable<ConfigSource> $sources
     */
    public function __construct(private iterable $sources)
    {
    }

    public function load(): SeleniumConfig
    {
        $sources = \is_array($this->sources) ? $this->sources : iterator_to_array($this->sources);
        usort($sources, static fn (ConfigSource $a, ConfigSource $b): int => $a->priority() <=> $b->priority());

        $values = [];
        foreach ($sources as $source) {
            $values = [...$values, ...$source->load()];
        }

        $browser = BrowserType::fromName($this->stringOr($values, 'browser-name', BrowserType::Chrome->value));

        return new SeleniumConfig(
            endpoint: new RemoteEndpoint(
                host: $this->stringOr($values, 'host', RemoteEndpoint::DEFAULT_HOST),
            ),
            browser: new BrowserConfig(
                browser: $browser,
                capabilities: $this->buildCapabilities($browser, $values),
            ),
            timeouts: new TimeoutConfig(
                implicitWaitMs: $this->intOr($values, 'timeout-implicit-ms', TimeoutConfig::DEFAULT_IMPLICIT_WAIT_MS),
                pageLoadMs: $this->intOr($values, 'timeout-page-load-ms', TimeoutConfig::DEFAULT_PAGE_LOAD_MS),
                scriptMs: $this->intOr($values, 'timeout-script-ms', TimeoutConfig::DEFAULT_SCRIPT_MS),
                defaultExplicitWaitMs: $this->intOr($values, 'timeout-explicit-ms', TimeoutConfig::DEFAULT_EXPLICIT_WAIT_MS),
            ),
            retry: new RetryConfig(
                maxAttempts: $this->intOr($values, 'retry-max-attempts', RetryConfig::DEFAULT_MAX_ATTEMPTS),
                initialDelayMs: $this->intOr($values, 'retry-initial-delay-ms', RetryConfig::DEFAULT_INITIAL_DELAY_MS),
                multiplier: $this->floatOr($values, 'retry-multiplier', RetryConfig::DEFAULT_MULTIPLIER),
            ),
            screenshot: new ScreenshotConfig(
                enabled: $this->boolOr($values, 'screenshot', false),
                path: $this->nullableString($values, 'screenshot-path'),
                mode: ScreenshotMode::from($this->stringOr($values, 'screenshot-mode', ScreenshotMode::OnFailure->value)),
                format: ScreenshotFormat::from($this->stringOr($values, 'screenshot-format', ScreenshotFormat::Png->value)),
            ),
            reporting: new ReportingConfig(
                allure: $this->boolOr($values, 'allure', false),
                reportPath: $this->nullableString($values, 'report-path'),
                videoEnabled: $this->boolOr($values, 'video-enabled', false),
            ),
        );
    }

    /**
     * @param array<string, scalar|array<mixed>|null> $values
     */
    private function buildCapabilities(BrowserType $browser, array $values): BrowserCapabilities
    {
        $args = $this->arrayOr($values, 'options');
        $browserVersion = $this->nullableString($values, 'browser-version');
        $platformName = $this->nullableString($values, 'platform-name');
        $acceptInsecureCerts = $this->boolOr($values, 'accept-insecure-certs', false);
        $pageLoadStrategy = $this->nullableString($values, 'page-load-strategy');
        $userAgent = $this->nullableString($values, 'user-agent');

        return match ($browser) {
            BrowserType::Chrome => new ChromeCapabilities(
                args: $args ?? ['--start-maximized', '--disable-infobars', '--disable-extensions'],
                browserVersion: $browserVersion,
                platformName: $platformName,
                acceptInsecureCerts: $acceptInsecureCerts,
                pageLoadStrategy: $pageLoadStrategy,
                userAgent: $userAgent,
            ),
            BrowserType::Firefox => new FirefoxCapabilities(
                args: $args ?? [],
                browserVersion: $browserVersion,
                platformName: $platformName,
                acceptInsecureCerts: $acceptInsecureCerts,
                pageLoadStrategy: $pageLoadStrategy,
                userAgent: $userAgent,
            ),
            BrowserType::Edge => new EdgeCapabilities(
                args: $args ?? ['--start-maximized'],
                browserVersion: $browserVersion,
                platformName: $platformName,
                acceptInsecureCerts: $acceptInsecureCerts,
                pageLoadStrategy: $pageLoadStrategy,
                userAgent: $userAgent,
            ),
            BrowserType::Safari => new SafariCapabilities(
                browserVersion: $browserVersion,
                platformName: $platformName ?? 'mac',
                acceptInsecureCerts: $acceptInsecureCerts,
                pageLoadStrategy: $pageLoadStrategy,
                userAgent: $userAgent,
            ),
        };
    }

    /**
     * @param array<string, scalar|array<mixed>|null> $values
     */
    private function stringOr(array $values, string $key, string $default): string
    {
        $value = $values[$key] ?? null;

        return \is_string($value) && $value !== '' ? $value : $default;
    }

    /**
     * @param array<string, scalar|array<mixed>|null> $values
     */
    private function nullableString(array $values, string $key): ?string
    {
        $value = $values[$key] ?? null;

        return \is_string($value) && $value !== '' ? $value : null;
    }

    /**
     * @param array<string, scalar|array<mixed>|null> $values
     */
    private function intOr(array $values, string $key, int $default): int
    {
        $value = $values[$key] ?? null;
        if (\is_int($value)) {
            return $value;
        }

        if (\is_string($value) && $value !== '' && ctype_digit(ltrim($value, '-'))) {
            return (int) $value;
        }

        return $default;
    }

    /**
     * @param array<string, scalar|array<mixed>|null> $values
     */
    private function floatOr(array $values, string $key, float $default): float
    {
        $value = $values[$key] ?? null;
        if (\is_float($value) || \is_int($value)) {
            return (float) $value;
        }

        if (\is_string($value) && is_numeric($value)) {
            return (float) $value;
        }

        return $default;
    }

    /**
     * @param array<string, scalar|array<mixed>|null> $values
     */
    private function boolOr(array $values, string $key, bool $default): bool
    {
        $value = $values[$key] ?? null;
        if (\is_bool($value)) {
            return $value;
        }

        if (\is_string($value)) {
            $filtered = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

            return $filtered ?? $default;
        }

        return $default;
    }

    /**
     * @param array<string, scalar|array<mixed>|null> $values
     *
     * @return list<string>|null
     */
    private function arrayOr(array $values, string $key): ?array
    {
        $value = $values[$key] ?? null;
        if (\is_array($value)) {
            return array_values(array_map(static fn (mixed $v): string => (string) $v, $value));
        }

        if (\is_string($value) && trim($value) !== '') {
            return array_values(array_map(trim(...), explode(',', $value)));
        }

        return null;
    }
}
