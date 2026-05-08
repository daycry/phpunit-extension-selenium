<?php

declare(strict_types=1);

namespace Daycry\PHPUnit\Selenium\Wait;

use Closure;
use Daycry\PHPUnit\Selenium\Locator\Locator;
use Daycry\PHPUnit\Selenium\Locator\LocatorResolver;
use Facebook\WebDriver\Exception\NoSuchElementException;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Throwable;

/**
 * Fluent wait builder. Each modifier returns a new builder; run() executes
 * the configured wait. The builder is intentionally immutable so an instance
 * can be reused (e.g. wait().forElement(X)) without leaking state.
 */
final readonly class WaitBuilder
{
    /**
     * @param Closure(RemoteWebDriver): mixed|null $condition
     */
    public function __construct(
        private RemoteWebDriver $driver,
        private LocatorResolver $resolver,
        private int $timeoutMs,
        private int $pollIntervalMs,
        private ?Closure $condition = null,
        private ?string $message = null,
    ) {
    }

    public function timeout(int $seconds): self
    {
        return $this->cloneWith(timeoutMs: $seconds * 1000);
    }

    public function timeoutMs(int $ms): self
    {
        return $this->cloneWith(timeoutMs: $ms);
    }

    public function pollEvery(int $ms): self
    {
        return $this->cloneWith(pollIntervalMs: $ms);
    }

    public function withMessage(string $message): self
    {
        return $this->cloneWith(message: $message);
    }

    public function forElement(Locator $locator): self
    {
        $by = $this->resolver->toBy($locator);

        return $this->cloneWith(
            condition: static fn (RemoteWebDriver $d) => (WebDriverExpectedCondition::presenceOfElementLocated($by)->getApply())($d),
            message: $this->message ?? \sprintf('Element %s not present', $locator->describe()),
        );
    }

    public function forElementGone(Locator $locator): self
    {
        $by = $this->resolver->toBy($locator);

        return $this->cloneWith(
            condition: static function (RemoteWebDriver $d) use ($by): bool {
                try {
                    return $d->findElements($by) === [];
                } catch (NoSuchElementException) {
                    return true;
                }
            },
            message: $this->message ?? \sprintf('Element %s still present', $locator->describe()),
        );
    }

    public function forVisible(Locator $locator): self
    {
        $by = $this->resolver->toBy($locator);

        return $this->cloneWith(
            condition: static fn (RemoteWebDriver $d) => (WebDriverExpectedCondition::visibilityOfElementLocated($by)->getApply())($d),
            message: $this->message ?? \sprintf('Element %s not visible', $locator->describe()),
        );
    }

    public function forHidden(Locator $locator): self
    {
        $by = $this->resolver->toBy($locator);

        return $this->cloneWith(
            condition: static fn (RemoteWebDriver $d) => (WebDriverExpectedCondition::invisibilityOfElementLocated($by)->getApply())($d),
            message: $this->message ?? \sprintf('Element %s still visible', $locator->describe()),
        );
    }

    public function forText(string $needle, ?Locator $within = null): self
    {
        $resolver = $this->resolver;

        return $this->cloneWith(
            condition: static function (RemoteWebDriver $d) use ($needle, $within, $resolver): bool {
                $haystack = $within instanceof Locator
                    ? $d->findElement($resolver->toBy($within))->getText()
                    : $d->findElement(WebDriverBy::tagName('body'))->getText();

                return str_contains($haystack, $needle);
            },
            message: $this->message ?? \sprintf('Text "%s" not found', $needle),
        );
    }

    public function forUrl(string $expected, bool $contains = true): self
    {
        return $this->cloneWith(
            condition: static fn (RemoteWebDriver $d): bool => $contains
                ? str_contains($d->getCurrentURL(), $expected)
                : $d->getCurrentURL() === $expected,
            message: $this->message ?? \sprintf('URL %s "%s"', $contains ? 'does not contain' : '!=', $expected),
        );
    }

    public function forTitle(string $expected, bool $contains = true): self
    {
        return $this->cloneWith(
            condition: static fn (RemoteWebDriver $d): bool => $contains
                ? str_contains($d->getTitle(), $expected)
                : $d->getTitle() === $expected,
            message: $this->message ?? \sprintf('Title %s "%s"', $contains ? 'does not contain' : '!=', $expected),
        );
    }

    /**
     * @param Closure(RemoteWebDriver): bool $callback
     */
    public function forFunction(Closure $callback): self
    {
        return $this->cloneWith(
            condition: static fn (RemoteWebDriver $d): bool => (bool) $callback($d),
            message: $this->message ?? 'Custom condition not satisfied',
        );
    }

    public function forAlert(): self
    {
        return $this->cloneWith(
            condition: static fn (RemoteWebDriver $d) => (WebDriverExpectedCondition::alertIsPresent()->getApply())($d),
            message: $this->message ?? 'Alert was not presented',
        );
    }

    public function run(): mixed
    {
        if (!$this->condition instanceof Closure) {
            throw new WaitTimeoutException('No wait condition was configured. Use forElement(), forText(), etc. before run().');
        }

        $deadline = $this->nowMs() + $this->timeoutMs;
        $lastError = null;

        while ($this->nowMs() <= $deadline) {
            try {
                $result = ($this->condition)($this->driver);
                if ($result !== false && $result !== null) {
                    return $result;
                }
            } catch (Throwable $e) {
                $lastError = $e;
            }

            usleep($this->pollIntervalMs * 1000);
        }

        throw new WaitTimeoutException(
            \sprintf('Wait timed out after %dms: %s', $this->timeoutMs, $this->message ?? 'condition not satisfied'),
            0,
            $lastError,
        );
    }

    private function cloneWith(
        ?int $timeoutMs = null,
        ?int $pollIntervalMs = null,
        ?Closure $condition = null,
        ?string $message = null,
    ): self {
        return new self(
            driver: $this->driver,
            resolver: $this->resolver,
            timeoutMs: $timeoutMs ?? $this->timeoutMs,
            pollIntervalMs: $pollIntervalMs ?? $this->pollIntervalMs,
            condition: $condition ?? $this->condition,
            message: $message ?? $this->message,
        );
    }

    private function nowMs(): int
    {
        return (int) (microtime(true) * 1000);
    }
}
