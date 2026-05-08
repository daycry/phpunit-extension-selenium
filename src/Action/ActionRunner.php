<?php

declare(strict_types=1);

namespace Daycry\PHPUnit\Selenium\Action;

use Closure;

interface ActionRunner
{
    /**
     * @template T
     * @param Closure(): T $action
     * @return T
     */
    public function run(string $name, Closure $action): mixed;
}
