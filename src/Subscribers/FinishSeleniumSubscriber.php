<?php

declare(strict_types=1);

namespace Daycry\PHPUnit\Selenium\Subscribers;

use PHPUnit\Event\TestRunner\Finished;
use PHPUnit\Event\TestRunner\FinishedSubscriber;

class FinishSeleniumSubscriber implements FinishedSubscriber
{
    public function notify(Finished $event): void
    {
        if (ConfigurationSubscriber::getDriver() !== null) {
            ConfigurationSubscriber::getDriver()->quit();
        }
    }
}