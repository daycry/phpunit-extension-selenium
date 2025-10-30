<?php

declare(strict_types=1);

namespace Daycry\PHPUnit\Selenium\Subscribers;

use Daycry\PHPUnit\Selenium\Libraries\SeleniumDriver;
use Daycry\PHPUnit\Selenium\Traits\AttributeResolverTrait;
use PHPUnit\Event\Test\Prepared;
use PHPUnit\Event\Test\PreparedSubscriber;

class StartSeleniumSubscriber implements PreparedSubscriber
{
    use AttributeResolverTrait;

    public function notify(Prepared $event): void
    {
        $test = $event->test();

        if ($this->needsSelenium($test)) {
            SeleniumDriver::initialize();
        }
    }
}
