<?php

declare(strict_types=1);

namespace Daycry\PHPUnit\Selenium\Action;

use Closure;

final class WebDriverRunner implements ActionRunner
{
    public function run(string $name, Closure $action): mixed
    {
        return $action();
    }
}
