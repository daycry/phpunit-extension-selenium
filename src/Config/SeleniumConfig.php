<?php

declare(strict_types=1);

namespace Daycry\PHPUnit\Selenium\Config;

final readonly class SeleniumConfig
{
    /**
     * @param array<string, SeleniumConfig> $profiles
     */
    public function __construct(
        public RemoteEndpoint $endpoint = new RemoteEndpoint(),
        public BrowserConfig $browser = new BrowserConfig(),
        public TimeoutConfig $timeouts = new TimeoutConfig(),
        public RetryConfig $retry = new RetryConfig(),
        public ScreenshotConfig $screenshot = new ScreenshotConfig(),
        public ReportingConfig $reporting = new ReportingConfig(),
        public array $profiles = [],
    ) {
    }

    public function withProfile(string $name): self
    {
        if (! isset($this->profiles[$name])) {
            return $this;
        }

        $profile = $this->profiles[$name];

        return new self(
            endpoint: $profile->endpoint,
            browser: $profile->browser,
            timeouts: $profile->timeouts,
            retry: $profile->retry,
            screenshot: $profile->screenshot,
            reporting: $profile->reporting,
            profiles: $this->profiles,
        );
    }
}
