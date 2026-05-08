<?php

declare(strict_types=1);

namespace Daycry\PHPUnit\Selenium\Browser;

use Closure;
use Daycry\PHPUnit\Selenium\Action\ActionRunner;
use Daycry\PHPUnit\Selenium\Action\WebDriverRunner;
use Daycry\PHPUnit\Selenium\Exception\SeleniumException;
use Daycry\PHPUnit\Selenium\Form\Date as FormDate;
use Daycry\PHPUnit\Selenium\Form\Select;
use Daycry\PHPUnit\Selenium\Form\Upload;
use Daycry\PHPUnit\Selenium\Locator\Locator;
use Daycry\PHPUnit\Selenium\Locator\LocatorResolver;
use Daycry\PHPUnit\Selenium\Session\SeleniumSession;
use Daycry\PHPUnit\Selenium\Storage\CookieJar;
use Daycry\PHPUnit\Selenium\Storage\Storage;
use Daycry\PHPUnit\Selenium\Wait\WaitBuilder;
use Facebook\WebDriver\Interactions\WebDriverActions;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\RemoteWebElement;
use Facebook\WebDriver\WebDriverDimension;
use Facebook\WebDriver\WebDriverSelect;
use Throwable;

/**
 * Chainable facade over RemoteWebDriver providing the v2 user-facing API.
 *
 * Mutating commands return $this to allow fluent chaining; queries (find,
 * findAll, currentUrl, title, ...) return values directly.
 */
final readonly class Browser
{
    public function __construct(
        private SeleniumSession $session,
        private LocatorResolver $resolver = new LocatorResolver(),
        private ActionRunner $runner = new WebDriverRunner(),
    ) {
    }

    public function session(): SeleniumSession
    {
        return $this->session;
    }

    public function driver(): RemoteWebDriver
    {
        return $this->session->driver();
    }

    public function visit(string $url): self
    {
        $this->runner->run('visit', fn (): mixed => $this->driver()->get($url));

        return $this;
    }

    public function currentUrl(): string
    {
        return $this->driver()->getCurrentURL();
    }

    public function title(): string
    {
        return $this->driver()->getTitle();
    }

    public function pageSource(): string
    {
        return $this->driver()->getPageSource();
    }

    public function refresh(): self
    {
        $this->driver()->navigate()->refresh();

        return $this;
    }

    public function back(): self
    {
        $this->driver()->navigate()->back();

        return $this;
    }

    public function forward(): self
    {
        $this->driver()->navigate()->forward();

        return $this;
    }

    public function resize(int $width, int $height): self
    {
        $this->driver()->manage()->window()->setSize(new WebDriverDimension($width, $height));

        return $this;
    }

    public function maximize(): self
    {
        $this->driver()->manage()->window()->maximize();

        return $this;
    }

    public function find(Locator $locator): Element
    {
        $by = $this->resolver->toBy($locator);
        $remote = $this->runner->run('find', fn () => $this->driver()->findElement($by));

        return new Element($locator, $remote);
    }

    /**
     * @return list<Element>
     */
    public function findAll(Locator $locator): array
    {
        $by = $this->resolver->toBy($locator);
        $remoteElements = $this->runner->run('findAll', fn (): array => $this->driver()->findElements($by));

        return array_map(static fn (RemoteWebElement $el): Element => new Element($locator, $el), $remoteElements);
    }

    public function exists(Locator $locator): bool
    {
        try {
            $by = $this->resolver->toBy($locator);

            return $this->driver()->findElements($by) !== [];
        } catch (Throwable) {
            return false;
        }
    }

    public function click(Locator $locator): self
    {
        $element = $this->find($locator);
        $this->runner->run('click', static function () use ($element): void {
            $element->remote()->click();
        });

        return $this;
    }

    public function type(Locator $locator, string $value, int $delayMs = 0): self
    {
        $element = $this->find($locator);
        $this->runner->run('type', function () use ($element, $value, $delayMs): void {
            if ($delayMs <= 0) {
                $element->remote()->sendKeys($value);

                return;
            }

            foreach (mb_str_split($value) as $char) {
                $element->remote()->sendKeys($char);
                usleep($delayMs * 1000);
            }
        });

        return $this;
    }

    public function clear(Locator $locator): self
    {
        $element = $this->find($locator);
        $this->runner->run('clear', static function () use ($element): void {
            $element->remote()->clear();
        });

        return $this;
    }

    public function fill(Locator $locator, string $value): self
    {
        return $this->clear($locator)->type($locator, $value);
    }

    public function check(Locator $locator): self
    {
        $element = $this->find($locator);
        if (! $element->isSelected()) {
            $element->remote()->click();
        }

        return $this;
    }

    public function uncheck(Locator $locator): self
    {
        $element = $this->find($locator);
        if ($element->isSelected()) {
            $element->remote()->click();
        }

        return $this;
    }

    public function pressKey(Key $key): self
    {
        $this->driver()->getKeyboard()->sendKeys($key->value);

        return $this;
    }

    public function hover(Locator $locator): self
    {
        $element = $this->find($locator);
        $this->actions()->moveToElement($element->remote())->perform();

        return $this;
    }

    public function dragTo(Locator $source, Locator $target): self
    {
        $sourceEl = $this->find($source);
        $targetEl = $this->find($target);
        $this->actions()->dragAndDrop($sourceEl->remote(), $targetEl->remote())->perform();

        return $this;
    }

    public function scrollTo(Locator $locator): self
    {
        $element = $this->find($locator);
        $this->driver()->executeScript('arguments[0].scrollIntoView({block:"center"});', [$element->remote()]);

        return $this;
    }

    public function upload(Locator $locator, string $path): self
    {
        if (! is_file($path)) {
            throw new SeleniumException(\sprintf('Upload target file does not exist: %s', $path));
        }

        $this->find($locator)->remote()->sendKeys($path);

        return $this;
    }

    /**
     * @param array<int|string, mixed> $args
     */
    public function executeScript(string $script, array $args = []): mixed
    {
        return $this->driver()->executeScript($script, $args);
    }

    public function select(Locator $locator, Select $option): self
    {
        $element = $this->find($locator);
        $select = new WebDriverSelect($element->remote());

        match ($option->strategy) {
            Select::BY_VALUE => $select->selectByValue((string) $option->value),
            Select::BY_LABEL => $select->selectByVisibleText((string) $option->value),
            Select::BY_INDEX => $select->selectByIndex((int) $option->value),
            default => throw new SeleniumException('Unknown Select strategy: ' . $option->strategy),
        };

        return $this;
    }

    /**
     * Fill multiple fields in one call. Each entry is a [Locator, value] tuple
     * where value is either a string, bool (checkbox), Select, Upload or Date.
     *
     * @param list<array{0: Locator, 1: mixed}> $fields
     */
    public function fillForm(array $fields): self
    {
        foreach ($fields as $field) {
            if (! isset($field[0]) || ! $field[0] instanceof Locator) {
                throw new SeleniumException('fillForm entries must be [Locator, value] tuples.');
            }

            $this->dispatchFormValue($field[0], $field[1] ?? null);
        }

        return $this;
    }

    public function cookies(): CookieJar
    {
        return new CookieJar($this->driver());
    }

    public function localStorage(): Storage
    {
        return new Storage($this->driver(), 'localStorage');
    }

    public function sessionStorage(): Storage
    {
        return new Storage($this->driver(), 'sessionStorage');
    }

    private function dispatchFormValue(Locator $locator, mixed $value): void
    {
        if ($value instanceof Select) {
            $this->select($locator, $value);

            return;
        }

        if ($value instanceof Upload) {
            $this->upload($locator, $value->path);

            return;
        }

        if ($value instanceof FormDate) {
            $this->fill($locator, $value->asString());

            return;
        }

        if (\is_bool($value)) {
            $value ? $this->check($locator) : $this->uncheck($locator);

            return;
        }

        $this->fill($locator, (string) $value);
    }

    public function wait(): WaitBuilder
    {
        return new WaitBuilder(
            driver: $this->driver(),
            resolver: $this->resolver,
            timeoutMs: $this->session->config->timeouts->defaultExplicitWaitMs,
            pollIntervalMs: $this->session->config->timeouts->pollIntervalMs,
        );
    }

    public function waitFor(Locator $locator, ?int $timeoutSeconds = null): self
    {
        $builder = $this->wait()->forElement($locator);
        if ($timeoutSeconds !== null) {
            $builder = $builder->timeout($timeoutSeconds);
        }
        $builder->run();

        return $this;
    }

    public function withinFrame(Locator $locator, Closure $callback): self
    {
        $element = $this->find($locator);
        $this->driver()->switchTo()->frame($element->remote());

        try {
            $callback($this);
        } finally {
            $this->driver()->switchTo()->defaultContent();
        }

        return $this;
    }

    public function acceptAlert(?string $expectedText = null): self
    {
        $alert = $this->driver()->switchTo()->alert();
        if ($expectedText !== null && $alert->getText() !== $expectedText) {
            throw new SeleniumException(\sprintf(
                'Alert text mismatch. Expected: "%s", got: "%s".',
                $expectedText,
                $alert->getText(),
            ));
        }
        $alert->accept();

        return $this;
    }

    public function dismissAlert(): self
    {
        $this->driver()->switchTo()->alert()->dismiss();

        return $this;
    }

    public function getAlertText(): string
    {
        return $this->driver()->switchTo()->alert()->getText();
    }

    private function actions(): WebDriverActions
    {
        return new WebDriverActions($this->driver());
    }
}
