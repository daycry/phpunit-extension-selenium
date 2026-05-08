<?php

declare(strict_types=1);

namespace Daycry\PHPUnit\Selenium\Subscriber;

use Daycry\PHPUnit\Selenium\Session\SeleniumContext;
use Daycry\PHPUnit\Selenium\Session\SessionManager;
use PHPUnit\Event\Code\TestMethod;
use PHPUnit\Event\Test\Finished;
use PHPUnit\Event\Test\FinishedSubscriber;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * On Test\Finished, pops the session off SeleniumContext and closes it.
 *
 * @internal
 */
final readonly class FinishTestSubscriber implements FinishedSubscriber
{
    public function __construct(
        private SessionManager $sessions,
        private LoggerInterface $logger = new NullLogger(),
    ) {
    }

    public function notify(Finished $event): void
    {
        $test = $event->test();

        if (! $test instanceof TestMethod) {
            return;
        }

        $testId = $test->id();

        if (! $this->sessions->has($testId)) {
            return;
        }

        if (SeleniumContext::hasCurrent()) {
            SeleniumContext::pop();
        }

        $this->sessions->close($testId);

        $this->logger->info('selenium.session.closed', [
            'test_id' => $testId,
        ]);
    }
}
