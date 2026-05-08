<?php

declare(strict_types=1);

namespace Daycry\PHPUnit\Selenium\Page;

use Daycry\PHPUnit\Selenium\Browser\Browser;
use Daycry\PHPUnit\Selenium\Locator\Locator;

/**
 * Base class for reusable page Components (header, modal, form section…).
 *
 * Components are scoped by a root locator so multiple instances can coexist
 * on the same page without stepping on each other's selectors.
 */
abstract class Component
{
    public function __construct(
        public readonly Browser $browser,
        public readonly Locator $root,
    ) {
    }
}
