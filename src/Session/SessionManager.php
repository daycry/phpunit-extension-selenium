<?php

declare(strict_types=1);

namespace Daycry\PHPUnit\Selenium\Session;

use Daycry\PHPUnit\Selenium\Config\ResolvedConfig;
use Daycry\PHPUnit\Selenium\Exception\SessionNotFoundException;

final class SessionManager
{
    /** @var array<string, SeleniumSession> */
    private array $sessions = [];

    public function __construct(private readonly WebDriverFactoryInterface $factory)
    {
    }

    public function start(string $testId, ResolvedConfig $config): SeleniumSession
    {
        if (isset($this->sessions[$testId])) {
            return $this->sessions[$testId];
        }

        $session = new SeleniumSession($testId, $config, $this->factory);
        $this->sessions[$testId] = $session;

        return $session;
    }

    public function get(string $testId): SeleniumSession
    {
        if (! isset($this->sessions[$testId])) {
            throw new SessionNotFoundException(\sprintf('No Selenium session registered for test "%s".', $testId));
        }

        return $this->sessions[$testId];
    }

    public function has(string $testId): bool
    {
        return isset($this->sessions[$testId]);
    }

    public function close(string $testId): void
    {
        if (! isset($this->sessions[$testId])) {
            return;
        }

        $this->sessions[$testId]->close();
        unset($this->sessions[$testId]);
    }

    public function closeAll(): void
    {
        foreach ($this->sessions as $session) {
            $session->close();
        }

        $this->sessions = [];
    }

    /**
     * @return array<string, SeleniumSession>
     */
    public function all(): array
    {
        return $this->sessions;
    }
}
