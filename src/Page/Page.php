<?php

declare(strict_types=1);

namespace Daycry\PHPUnit\Selenium\Page;

use Daycry\PHPUnit\Selenium\Asserts\SeleniumAssertions;
use Daycry\PHPUnit\Selenium\Browser\Browser;

/**
 * Base class for Page Objects.
 *
 * Subclasses declare their URL via {@see url()}, expose typed Locator
 * properties as the page contract, and may override {@see assertOnPage()}
 * to encode page-specific invariants.
 */
abstract class Page
{
    use SeleniumAssertions;

    public function __construct(public readonly Browser $browser)
    {
    }

    /**
     * Path or absolute URL the page lives at. Used by {@see visit()} and
     * {@see assertOnPage()} as default.
     */
    abstract public function url(): string;

    public function visit(): static
    {
        $this->browser->visit($this->url());

        return $this;
    }

    public function assertOnPage(string $message = ''): static
    {
        $this->assertUrlContains($this->url(), $message);

        return $this;
    }

    /**
     * Satisfies the contract from SeleniumAssertions so the trait can run its
     * assertions through the Browser injected at construction time.
     */
    protected function browser(): Browser
    {
        return $this->browser;
    }
}
