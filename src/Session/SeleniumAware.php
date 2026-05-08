<?php

declare(strict_types=1);

namespace Daycry\PHPUnit\Selenium\Session;

use Daycry\PHPUnit\Selenium\Browser\Browser;

/**
 * Trait used by tests to access the current Selenium session and a ready-to-use
 * Browser facade without relying on a static singleton. The session is
 * push/popped by the lifecycle subscribers, so {@see selenium()} returns the
 * session bound to the currently running test.
 */
trait SeleniumAware
{
    private ?Browser $cachedBrowser = null;

    protected function selenium(): SeleniumSession
    {
        return SeleniumContext::current();
    }

    /**
     * Lazily builds a Browser facade bound to the current test session.
     * Cached for the lifetime of this test instance so calls are cheap.
     */
    protected function browser(): Browser
    {
        if ($this->cachedBrowser === null || $this->cachedBrowser->session() !== $this->selenium()) {
            $this->cachedBrowser = new Browser($this->selenium());
        }

        return $this->cachedBrowser;
    }
}
