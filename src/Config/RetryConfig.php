<?php

declare(strict_types=1);

namespace Daycry\PHPUnit\Selenium\Config;

use Daycry\PHPUnit\Selenium\Exception\ConfigurationException;
use Facebook\WebDriver\Exception\ElementNotInteractableException;
use Facebook\WebDriver\Exception\NoSuchElementException;
use Facebook\WebDriver\Exception\StaleElementReferenceException;
use Throwable;

final readonly class RetryConfig
{
    public const int DEFAULT_MAX_ATTEMPTS = 1;
    public const int DEFAULT_INITIAL_DELAY_MS = 100;
    public const float DEFAULT_MULTIPLIER = 2.0;
    public const int DEFAULT_MAX_DELAY_MS = 5_000;
    public const float DEFAULT_JITTER = 0.1;

    /**
     * @param list<class-string<Throwable>> $retryableExceptions
     */
    public function __construct(
        public int $maxAttempts = self::DEFAULT_MAX_ATTEMPTS,
        public int $initialDelayMs = self::DEFAULT_INITIAL_DELAY_MS,
        public float $multiplier = self::DEFAULT_MULTIPLIER,
        public int $maxDelayMs = self::DEFAULT_MAX_DELAY_MS,
        public float $jitter = self::DEFAULT_JITTER,
        public array $retryableExceptions = [
            StaleElementReferenceException::class,
            NoSuchElementException::class,
            ElementNotInteractableException::class,
        ],
    ) {
        if ($this->maxAttempts < 1) {
            throw new ConfigurationException(\sprintf('maxAttempts must be >= 1, got %d.', $this->maxAttempts));
        }

        if ($this->initialDelayMs < 0) {
            throw new ConfigurationException(\sprintf('initialDelayMs must be >= 0, got %d.', $this->initialDelayMs));
        }

        if ($this->multiplier < 1.0) {
            throw new ConfigurationException(\sprintf('multiplier must be >= 1.0, got %.2f.', $this->multiplier));
        }

        if ($this->maxDelayMs < $this->initialDelayMs) {
            throw new ConfigurationException(
                \sprintf('maxDelayMs (%d) must be >= initialDelayMs (%d).', $this->maxDelayMs, $this->initialDelayMs),
            );
        }

        if ($this->jitter < 0.0 || $this->jitter > 1.0) {
            throw new ConfigurationException(\sprintf('jitter must be in [0.0, 1.0], got %.2f.', $this->jitter));
        }
    }
}
