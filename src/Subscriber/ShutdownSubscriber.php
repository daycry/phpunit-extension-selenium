<?php

declare(strict_types=1);

namespace Daycry\PHPUnit\Selenium\Subscriber;

use Daycry\PHPUnit\Selenium\Session\SessionManager;
use PHPUnit\Event\TestRunner\ExecutionFinished;
use PHPUnit\Event\TestRunner\ExecutionFinishedSubscriber;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Defensive cleanup at the end of the test run: ensures every session is
 * closed even if individual FinishTest events did not fire (e.g. abrupt
 * termination, runner errors).
 *
 * @internal
 */
final readonly class ShutdownSubscriber implements ExecutionFinishedSubscriber
{
    public function __construct(
        private SessionManager $sessions,
        private LoggerInterface $logger = new NullLogger(),
    ) {
    }

    public function notify(ExecutionFinished $event): void
    {
        $count = \count($this->sessions->all());
        if ($count > 0) {
            $this->logger->notice('selenium.shutdown.cleanup', ['leaked_sessions' => $count]);
        }

        $this->sessions->closeAll();
    }
}
