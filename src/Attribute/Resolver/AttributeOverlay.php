<?php

declare(strict_types=1);

namespace Daycry\PHPUnit\Selenium\Attribute\Resolver;

use Daycry\PHPUnit\Selenium\Browser\BrowserType;
use Daycry\PHPUnit\Selenium\Browser\Capabilities\BrowserCapabilities;
use Daycry\PHPUnit\Selenium\Browser\Capabilities\ChromeCapabilities;
use Daycry\PHPUnit\Selenium\Browser\Capabilities\EdgeCapabilities;
use Daycry\PHPUnit\Selenium\Browser\Capabilities\FirefoxCapabilities;
use Daycry\PHPUnit\Selenium\Browser\Capabilities\SafariCapabilities;
use Daycry\PHPUnit\Selenium\Config\BrowserConfig;
use Daycry\PHPUnit\Selenium\Config\ResolvedConfig;
use Daycry\PHPUnit\Selenium\Config\RetryConfig;
use Daycry\PHPUnit\Selenium\Config\ScreenshotConfig;
use Daycry\PHPUnit\Selenium\Config\SeleniumConfig;
use Daycry\PHPUnit\Selenium\Config\TimeoutConfig;

/**
 * Applies `#[UseSelenium(...)]` overrides on top of a base SeleniumConfig
 * to produce the per-test ResolvedConfig used to start a session.
 */
final class AttributeOverlay
{
    public function apply(SeleniumConfig $base, ?ResolvedAttributes $attributes): ResolvedConfig
    {
        $config = $base;

        if ($attributes instanceof ResolvedAttributes && $attributes->effective->profile !== null) {
            $config = $config->withProfile($attributes->effective->profile);
        }

        if (!$attributes instanceof ResolvedAttributes) {
            return ResolvedConfig::fromBase($config);
        }

        $effective = $attributes->effective;

        $browser = $effective->browser !== null ? BrowserType::fromName($effective->browser) : $config->browser->browser;

        $capabilities = $this->mergeCapabilities(
            $config->browser->capabilities,
            $browser,
            $effective->browserVersion,
            $effective->platform,
            $effective->capabilities,
        );

        $browserConfig = new BrowserConfig($browser, $capabilities);

        $timeouts = $this->applyTimeouts($config->timeouts, $effective->timeoutSeconds, $effective->pageLoadTimeoutMs);
        $retry = $this->applyRetry($config->retry, $effective->retryAttempts);
        $screenshot = $this->applyScreenshot($config->screenshot, $effective->screenshot);

        return new ResolvedConfig(
            endpoint: $config->endpoint,
            browser: $browserConfig,
            timeouts: $timeouts,
            retry: $retry,
            screenshot: $screenshot,
            reporting: $config->reporting,
            tags: $effective->tags === [] ? [] : ['tags' => $effective->tags],
        );
    }

    /**
     * @param array<string, scalar|array<mixed>> $rawCapabilities
     */
    private function mergeCapabilities(
        BrowserCapabilities $current,
        BrowserType $browser,
        ?string $browserVersion,
        ?string $platform,
        array $rawCapabilities,
    ): BrowserCapabilities {
        $browserVersion ??= $current->browserVersion;
        $platform ??= $current->platformName;
        $extra = [...$current->extra, ...$rawCapabilities];

        return match ($browser) {
            BrowserType::Chrome => new ChromeCapabilities(
                args: $current instanceof ChromeCapabilities ? $current->args : ['--start-maximized'],
                prefs: $current instanceof ChromeCapabilities ? $current->prefs : [],
                binary: $current instanceof ChromeCapabilities ? $current->binary : null,
                browserVersion: $browserVersion,
                platformName: $platform,
                acceptInsecureCerts: $current->acceptInsecureCerts,
                pageLoadStrategy: $current->pageLoadStrategy,
                userAgent: $current->userAgent,
                extra: $extra,
            ),
            BrowserType::Firefox => new FirefoxCapabilities(
                args: $current instanceof FirefoxCapabilities ? $current->args : [],
                prefs: $current instanceof FirefoxCapabilities ? $current->prefs : [],
                profileDir: $current instanceof FirefoxCapabilities ? $current->profileDir : null,
                binary: $current instanceof FirefoxCapabilities ? $current->binary : null,
                browserVersion: $browserVersion,
                platformName: $platform,
                acceptInsecureCerts: $current->acceptInsecureCerts,
                pageLoadStrategy: $current->pageLoadStrategy,
                userAgent: $current->userAgent,
                extra: $extra,
            ),
            BrowserType::Edge => new EdgeCapabilities(
                args: $current instanceof EdgeCapabilities ? $current->args : ['--start-maximized'],
                prefs: $current instanceof EdgeCapabilities ? $current->prefs : [],
                useChromium: ! $current instanceof EdgeCapabilities || $current->useChromium,
                browserVersion: $browserVersion,
                platformName: $platform,
                acceptInsecureCerts: $current->acceptInsecureCerts,
                pageLoadStrategy: $current->pageLoadStrategy,
                userAgent: $current->userAgent,
                extra: $extra,
            ),
            BrowserType::Safari => new SafariCapabilities(
                technologyPreview: $current instanceof SafariCapabilities && $current->technologyPreview,
                browserVersion: $browserVersion,
                platformName: $platform ?? 'mac',
                acceptInsecureCerts: $current->acceptInsecureCerts,
                pageLoadStrategy: $current->pageLoadStrategy,
                userAgent: $current->userAgent,
                extra: $extra,
            ),
        };
    }

    private function applyTimeouts(TimeoutConfig $current, ?int $timeoutSeconds, ?int $pageLoadMs): TimeoutConfig
    {
        if ($timeoutSeconds === null && $pageLoadMs === null) {
            return $current;
        }

        return new TimeoutConfig(
            implicitWaitMs: $current->implicitWaitMs,
            pageLoadMs: $pageLoadMs ?? $current->pageLoadMs,
            scriptMs: $current->scriptMs,
            defaultExplicitWaitMs: $timeoutSeconds !== null ? $timeoutSeconds * 1000 : $current->defaultExplicitWaitMs,
            pollIntervalMs: $current->pollIntervalMs,
        );
    }

    private function applyRetry(RetryConfig $current, ?int $maxAttempts): RetryConfig
    {
        if ($maxAttempts === null) {
            return $current;
        }

        return new RetryConfig(
            maxAttempts: $maxAttempts,
            initialDelayMs: $current->initialDelayMs,
            multiplier: $current->multiplier,
            maxDelayMs: $current->maxDelayMs,
            jitter: $current->jitter,
            retryableExceptions: $current->retryableExceptions,
        );
    }

    private function applyScreenshot(ScreenshotConfig $current, ?bool $enabled): ScreenshotConfig
    {
        if ($enabled === null) {
            return $current;
        }

        return new ScreenshotConfig(
            enabled: $enabled,
            path: $current->path,
            mode: $current->mode,
            format: $current->format,
        );
    }
}
