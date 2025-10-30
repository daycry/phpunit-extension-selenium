<?php

declare(strict_types=1);

namespace Daycry\PHPUnit\Selenium\Subscribers;

use Daycry\PHPUnit\Selenium\Libraries\SeleniumDriver;
use Daycry\PHPUnit\Selenium\Traits\AttributeResolverTrait;
use Daycry\PHPUnit\Selenium\Traits\SeleniumActions;
use PHPUnit\Event\Test\Failed;
use PHPUnit\Event\Test\FailedSubscriber as PHPUnitFailedSubscriber;

// La extensión debe implementar las interfaces de Hook que desea escuchar
final class FailedSubscriber implements PHPUnitFailedSubscriber
{
    use AttributeResolverTrait;
    use SeleniumActions;

    /**
     * Se llama cuando un test falla (AssertionFailedError o Failure).
     */
    public function notify(Failed $event): void
    {
        if ($this->needsSelenium($event->test())) {
            $test = $event->test();
            $throwable = $event->throwable();

            // Obtén el nombre completo del test que falló
            $testName = $test->id();

            // Obtén la descripción de la falla
            $failureMessage = $throwable->message();

            // Aquí pones la lógica que quieres ejecutar
            //echo "\n\n🚨 ¡TEST FALLIDO! 🚨\n";
            //echo "Test: " . $testName . "\n";
            //echo "Mensaje de Falla: " . $failureMessage . "\n";

            $screenshotPath = SeleniumDriver::getScreenshotPath();

            if ($screenshotPath !== null && SeleniumDriver::getDriver() !== null) {
                $fileName = str_replace(['\\', '::'], '_', $testName) . '_' . date('Ymd_His') . '.png';
                $this->takeScreenshot($fileName);
            }

            // Puedes loguear la falla, enviar una notificación, etc.
        }

        return;
    }
}
