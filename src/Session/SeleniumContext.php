<?php

declare(strict_types=1);

namespace Daycry\PHPUnit\Selenium\Session;

use Daycry\PHPUnit\Selenium\Exception\SessionNotFoundException;

/**
 * Scoped per-test session accessor.
 *
 * Push a session onto the stack at test start and pop it at test end. Tests can
 * fetch the session for the currently running test via {@see current()} without
 * relying on a globally mutable singleton.
 */
final class SeleniumContext
{
    /** @var list<SeleniumSession> */
    private static array $stack = [];

    private static ?SessionManager $manager = null;

    public static function bind(SessionManager $manager): void
    {
        self::$manager = $manager;
    }

    public static function manager(): SessionManager
    {
        if (!self::$manager instanceof SessionManager) {
            throw new SessionNotFoundException('SessionManager has not been bound to SeleniumContext.');
        }

        return self::$manager;
    }

    public static function push(SeleniumSession $session): void
    {
        self::$stack[] = $session;
    }

    public static function pop(): ?SeleniumSession
    {
        return array_pop(self::$stack);
    }

    public static function current(): SeleniumSession
    {
        $session = end(self::$stack);
        if ($session === false) {
            throw new SessionNotFoundException('No Selenium session is currently active.');
        }

        return $session;
    }

    public static function hasCurrent(): bool
    {
        return self::$stack !== [];
    }

    public static function reset(): void
    {
        self::$stack = [];
        self::$manager = null;
    }
}
