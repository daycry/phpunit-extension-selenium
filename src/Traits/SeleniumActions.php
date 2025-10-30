<?php

declare(strict_types=1);

namespace Daycry\PHPUnit\Selenium\Traits;

use Closure;
use Daycry\PHPUnit\Selenium\Libraries\SeleniumDriver;
use Facebook\WebDriver\Exception\WebDriverException;
use Facebook\WebDriver\Remote\RemoteWebElement;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;

trait SeleniumActions
{
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
    }

    protected function takeScreenshot(string $filename): void
    {
        try {
            if (SeleniumDriver::getDriver() === null) {
                throw new WebDriverException('WebDriver instance is not available.');
            }

            if (SeleniumDriver::getScreenshotPath() !== null) {
                $screenshotDir = SeleniumDriver::getScreenshotPath();
                if (! is_dir($screenshotDir)) {
                    mkdir($screenshotDir, 0o777, true);
                }
                SeleniumDriver::getDriver()->takeScreenshot(rtrim($screenshotDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $filename);
            }
        } catch (WebDriverException $se) {
            //echo "\n[SCREENSHOT] Failed to save screenshot: {$se->getMessage()}\n";
        }
    }

    // attr = cssSelector, id, name, xpath, className, tagName, linkText, partialLinkText
    // cssSelector('button.btn-primary')
    // xpath("//button[text()='Iniciar Sesión']")
    protected function clickElementBy(string $key, $attr = 'id', ?Closure $callback = null): void
    {
        SeleniumDriver::getDriver()->wait(10)->until(
            WebDriverExpectedCondition::elementToBeClickable(WebDriverBy::{$attr}($key)),
        );
        $button = SeleniumDriver::getDriver()->findElement(WebDriverBy::{$attr}($key));
        $this->scrollToElement($button);
        $button->click();
    }

    protected function fillFieldBy(string $key, string $value, string $attr = 'id', int $delay = 25): void
    {
        $this->waitElement($key, $attr);

        $inputField = SeleniumDriver::getDriver()->findElement(WebDriverBy::{$attr}($key));
        $this->scrollToElement($inputField);
        $inputField->clear();
        // Escribe carácter por carácter con retardo en milisegundos
        $chars = mb_str_split($value);

        foreach ($chars as $char) {
            $inputField->sendKeys($char);
            usleep($delay * 1000); // convierte a microsegundos
        }
    }

    protected function goToUrl(string $url): void
    {
        SeleniumDriver::getDriver()->get($url);
    }

    protected function waitElement(string $key, string $attr, ?string $compareText = null): void
    {
        SeleniumDriver::getDriver()->wait(10)->until(
            WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::{$attr}($key)),
        );

        if ($compareText) {
            $successText = SeleniumDriver::getDriver()->findElement(WebDriverBy::{$attr}($key))->getText();
            $this->assertStringContainsString($compareText, $successText);
        }
    }

    protected function waitPageLoaded(string $urlPart, int $timeout = 10): void
    {
        SeleniumDriver::getDriver()->wait($timeout)->until(
            // Opción A: Esperar a que un elemento (ej. el título del dashboard) sea visible
            /*WebDriverExpectedCondition::visibilityOfElementLocated(
                WebDriverBy::id('dashboard-title')
            )*/
            WebDriverExpectedCondition::urlContains($urlPart),
        );

        // 3. El test continúa aquí: ¡la pantalla está lista!
        $this->assertStringContainsString($urlPart, SeleniumDriver::getDriver()->getCurrentURL(), "No se redireccionó a {$urlPart}.");
    }

    private function scrollToElement(RemoteWebElement $element): void
    {
        SeleniumDriver::getDriver()->executeScript('arguments[0].scrollIntoView(true);', [$element]);
    }
}
