<?php

declare(strict_types=1);

namespace Daycry\PHPUnit\Selenium\Action;

use Closure;
use Daycry\PHPUnit\Selenium\Retry\RetryPolicy;
use Throwable;

final readonly class RetryingRunner implements ActionRunner
{
    public function __construct(
        private ActionRunner $inner,
        private RetryPolicy $policy,
    ) {
    }

    public function run(string $name, Closure $action): mixed
    {
        $attempt = 0;

        while (true) {
            ++$attempt;

            try {
                return $this->inner->run($name, $action);
            } catch (Throwable $e) {
                if (! $this->policy->shouldRetry($e, $attempt)) {
                    throw $e;
                }

                $this->policy->sleep($this->policy->delayFor($attempt));
            }
        }
    }
}
