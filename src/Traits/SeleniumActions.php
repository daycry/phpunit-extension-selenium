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
    protected function clickElementBy(string $key, $attr = 'id'): void
    {
        SeleniumDriver::getDriver()->wait(30)->until(
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

    protected function waitElement(string $key, string $attr, ?array $options = null): void
    {
        try {
            SeleniumDriver::getDriver()->wait(30)->until(
                WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::{$attr}($key)),
            );
        } catch (\Facebook\WebDriver\Exception\TimeoutException $e) {
            // Aquí puedes lanzar tu propia excepción con un mensaje mejorado
            throw new \Exception("Step fallido: No se encontró el elemento '$key' ($attr) tras esperar.", 0, $e);
        }

        if($options && isset($options['notHasContentAttribute']) && is_array($options['notHasContentAttribute']))
        {
            SeleniumDriver::getDriver()->wait(30)->until(function() use ($key, $attr,$options) {
                try {
                    $el = SeleniumDriver::getDriver()->findElement(WebDriverBy::{$attr}($key));
                    $values = $el->getAttribute($options['notHasContentAttribute']['key']) ?? '';
                    return !preg_match('/\b' . preg_quote($options['notHasContentAttribute']['value'], '/') . '\b/', $values);
                } catch (WebDriverException $e) {

                }
            });
        }

        if ($options && isset($options['compareText'])) {
            $compareText = $options['compareText'];
            $successText = SeleniumDriver::getDriver()->findElement(WebDriverBy::{$attr}($key))->getText();
            $this->assertStringContainsString($compareText, $successText);
        }
    }

    protected function getValueFromElement(string $key, string $type = 'id', string $attr = 'value', ?array $options = null): string
    {
        $this->waitElement($key, $type, $options);

        $inputField = SeleniumDriver::getDriver()->findElement(WebDriverBy::{$type}($key));

        return $inputField->getAttribute($attr);
    }

    protected function waitDialogUntilOpen(string $key, string $attr): void
    {
        $dialog = SeleniumDriver::getDriver()->wait(30)->until(
            WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::{$attr}($key)),
        );

        SeleniumDriver::getDriver()->wait(30)->until(
            function() use ($dialog) {
                //return $dialog->isDisplayed();
                return SeleniumDriver::getDriver()->executeScript("return arguments[0].open === true;", [$dialog]);
            }
        );
    }

    protected function waitPageLoaded(string $urlPart, int $timeout = 30): void
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
