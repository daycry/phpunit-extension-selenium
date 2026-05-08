<?php

declare(strict_types=1);

namespace Daycry\PHPUnit\Selenium\Screenshot;

use Daycry\PHPUnit\Selenium\Config\ScreenshotConfig;
use Daycry\PHPUnit\Selenium\Config\ScreenshotMode;
use Daycry\PHPUnit\Selenium\Exception\ScreenshotException;
use Daycry\PHPUnit\Selenium\Session\SeleniumSession;
use Facebook\WebDriver\Exception\WebDriverException;
use Throwable;

final readonly class ScreenshotService
{
    public function __construct(
        public ScreenshotConfig $config,
        private FilenameSanitizer $sanitizer = new FilenameSanitizer(),
    ) {
    }

    public function capture(SeleniumSession $session, string $className, string $methodName, string $status): ?string
    {
        if (! $this->config->isOn()) {
            return null;
        }

        if ($this->config->mode === ScreenshotMode::OnFailure && $status !== 'failed' && $status !== 'errored') {
            return null;
        }

        $directory = $this->config->path ?? sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'selenium-screenshots';
        if (! is_dir($directory) && ! @mkdir($directory, 0o777, true) && ! is_dir($directory)) {
            throw new ScreenshotException(\sprintf('Unable to create screenshot directory: %s', $directory));
        }

        $sessionId = null;
        try {
            $sessionId = $session->driver()->getSessionID();
        } catch (Throwable) {
            // session may not be ready; continue without id
        }

        $browserName = $session->config->browser->browser->value;

        $filename = $this->sanitizer->build(
            className: $className,
            methodName: $methodName,
            browser: $browserName,
            status: $status,
            sessionId: $sessionId,
            extension: $this->config->format->value,
        );

        $path = rtrim($directory, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $filename;

        try {
            $session->driver()->takeScreenshot($path);
        } catch (WebDriverException $e) {
            throw new ScreenshotException(\sprintf('Failed to capture screenshot: %s', $e->getMessage()), 0, $e);
        }

        return $path;
    }
}
