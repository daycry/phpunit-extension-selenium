<?php

declare(strict_types=1);

namespace Daycry\PHPUnit\Selenium\Subscriber;

use Daycry\PHPUnit\Selenium\Attribute\Resolver\AttributeOverlay;
use Daycry\PHPUnit\Selenium\Attribute\Resolver\ResolvedAttributes;
use Daycry\PHPUnit\Selenium\Attribute\Resolver\TestAttributeResolver;
use Daycry\PHPUnit\Selenium\Config\SeleniumConfig;
use Daycry\PHPUnit\Selenium\Session\SeleniumContext;
use Daycry\PHPUnit\Selenium\Session\SessionManager;
use PHPUnit\Event\Code\TestMethod;
use PHPUnit\Event\Test\Prepared;
use PHPUnit\Event\Test\PreparedSubscriber;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Throwable;

/**
 * On Test\Prepared, resolves UseSelenium attributes for the test method,
 * applies them on top of the base config and starts the session, pushing it
 * onto SeleniumContext.
 *
 * @internal
 */
final readonly class StartTestSubscriber implements PreparedSubscriber
{
    public function __construct(
        private SeleniumConfig $config,
        private SessionManager $sessions,
        private TestAttributeResolver $resolver,
        private AttributeOverlay $overlay = new AttributeOverlay(),
        private LoggerInterface $logger = new NullLogger(),
    ) {
    }

    public function notify(Prepared $event): void
    {
        $test = $event->test();

        if (! $test instanceof TestMethod) {
            return;
        }

        /** @var class-string $className */
        $className = $test->className();
        $methodName = $test->methodName();
        $attributes = $this->resolver->resolve($className, $methodName);

        if (!$attributes instanceof ResolvedAttributes) {
            return;
        }

        $resolvedConfig = $this->overlay->apply($this->config, $attributes);
        $testId = $test->id();

        try {
            $session = $this->sessions->start($testId, $resolvedConfig);
            SeleniumContext::push($session);
            $session->start();

            $this->logger->info('selenium.session.started', [
                'test_id' => $testId,
                'browser' => $resolvedConfig->browser->browser->value,
            ]);
        } catch (Throwable $e) {
            $this->logger->error('selenium.session.start_failed', [
                'test_id' => $testId,
                'exception' => $e::class,
                'message' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
