<?php

declare(strict_types=1);

namespace Daycry\PHPUnit\Selenium\Config;

/**
 * Per-test materialised configuration.
 *
 * Produced by applying an attribute overlay (e.g. #[UseSelenium(browser: 'firefox')])
 * on top of a base SeleniumConfig. Carries the effective values used to start a
 * single Selenium session.
 */
final readonly class ResolvedConfig
{
    public function __construct(
        public RemoteEndpoint $endpoint,
        public BrowserConfig $browser,
        public TimeoutConfig $timeouts,
        public RetryConfig $retry,
        public ScreenshotConfig $screenshot,
        public ReportingConfig $reporting,
        /** @var array<string, scalar|array<mixed>> */
        public array $tags = [],
    ) {
    }

    public static function fromBase(SeleniumConfig $base): self
    {
        return new self(
            endpoint: $base->endpoint,
            browser: $base->browser,
            timeouts: $base->timeouts,
            retry: $base->retry,
            screenshot: $base->screenshot,
            reporting: $base->reporting,
        );
    }
}
