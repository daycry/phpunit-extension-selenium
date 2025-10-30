<?php

declare(strict_types=1);

namespace Daycry\PHPUnit\Selenium\Subscribers;

use Daycry\PHPUnit\Selenium\Libraries\SeleniumDriver;
use PHPUnit\Event\TestRunner\Finished;
use PHPUnit\Event\TestRunner\FinishedSubscriber;

class FinishSeleniumSubscriber implements FinishedSubscriber
{
    public function notify(Finished $event): void
    {
        if (SeleniumDriver::getDriver(false) !== null) {
            SeleniumDriver::getDriver()->quit();
        }
    }
}
