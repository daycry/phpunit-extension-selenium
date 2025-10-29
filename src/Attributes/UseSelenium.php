<?php

declare(strict_types=1);

namespace Daycry\PHPUnit\Selenium\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
final class UseSelenium {
    public function __construct()
    {
    }
}
