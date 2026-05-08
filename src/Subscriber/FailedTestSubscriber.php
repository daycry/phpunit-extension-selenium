<?php

declare(strict_types=1);

namespace Daycry\PHPUnit\Selenium\Subscriber;

use Daycry\PHPUnit\Selenium\Reporting\AllureReporter;
use Daycry\PHPUnit\Selenium\Reporting\BrowserLogCollector;
use Daycry\PHPUnit\Selenium\Screenshot\ScreenshotService;
use Daycry\PHPUnit\Selenium\Session\SessionManager;
use PHPUnit\Event\Code\TestMethod;
use PHPUnit\Event\Test\Failed;
use PHPUnit\Event\Test\FailedSubscriber;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Throwable;

/**
 * On Test\Failed: captures a screenshot, gathers browser console logs,
 * forwards them to Allure when available, and emits a structured warning
 * log entry.
 *
 * @internal
 */
final readonly class FailedTestSubscriber implements FailedSubscriber
{
    public function __construct(
        private SessionManager $sessions,
        private ScreenshotService $screenshots,
        private LoggerInterface $logger = new NullLogger(),
        private ?AllureReporter $allure = null,
        private ?BrowserLogCollector $logs = null,
    ) {
    }

    public function notify(Failed $event): void
    {
        $test = $event->test();

        if (! $test instanceof TestMethod) {
            return;
        }

        $testId = $test->id();

        if (! $this->sessions->has($testId)) {
            return;
        }

        $session = $this->sessions->get($testId);
        $screenshotPath = null;

        try {
            $screenshotPath = $this->screenshots->capture(
                session: $session,
                className: $test->className(),
                methodName: $test->methodName(),
                status: 'failed',
            );
        } catch (Throwable $e) {
            $this->logger->warning('selenium.test.failed.screenshot_error', [
                'test_id' => $testId,
                'exception' => $e::class,
                'message' => $e->getMessage(),
            ]);
        }

        if ($screenshotPath !== null) {
            $this->allure?->attachFile('screenshot', $screenshotPath);
        }

        if ($this->allure?->isAvailable() === true && $this->logs instanceof BrowserLogCollector && $session->isStarted()) {
            try {
                $logs = $this->logs->collect($session->driver());
                $this->allure->attachText('browser-console', json_encode($logs, JSON_PRETTY_PRINT) ?: '', 'application/json');
            } catch (Throwable) {
                // attachments are best-effort
            }
        }

        $this->logger->warning('selenium.test.failed', [
            'test_id' => $testId,
            'screenshot' => $screenshotPath,
        ]);
    }
}
