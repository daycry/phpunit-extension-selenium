<?php

declare(strict_types=1);

namespace Daycry\PHPUnit\Selenium\Subscriber;

use Daycry\PHPUnit\Selenium\Config\SeleniumConfig;
use Daycry\PHPUnit\Selenium\Session\SeleniumContext;
use Daycry\PHPUnit\Selenium\Session\SessionManager;
use PHPUnit\Event\TestRunner\ExecutionStarted;
use PHPUnit\Event\TestRunner\ExecutionStartedSubscriber;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Binds the SessionManager into SeleniumContext so tests can resolve the
 * current session, and emits an info-level "ready" log entry.
 *
 * @internal
 */
final readonly class BootstrapSubscriber implements ExecutionStartedSubscriber
{
    public function __construct(
        private SeleniumConfig $config,
        private SessionManager $sessions,
        private LoggerInterface $logger = new NullLogger(),
    ) {
    }

    public function notify(ExecutionStarted $event): void
    {
        SeleniumContext::bind($this->sessions);

        $this->logger->info('selenium.bootstrap', [
            'host' => $this->config->endpoint->host,
            'browser' => $this->config->browser->browser->value,
        ]);
    }
}
