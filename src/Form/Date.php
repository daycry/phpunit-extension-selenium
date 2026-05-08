<?php

declare(strict_types=1);

namespace Daycry\PHPUnit\Selenium\Form;

use DateTimeImmutable;
use DateTimeInterface;

final readonly class Date
{
    private function __construct(public DateTimeImmutable $value, public string $format)
    {
    }

    public static function of(string $iso8601, string $format = 'Y-m-d'): self
    {
        $datetime = DateTimeImmutable::createFromFormat('Y-m-d', $iso8601)
            ?: new DateTimeImmutable($iso8601);

        return new self($datetime, $format);
    }

    public static function fromDateTime(DateTimeInterface $dt, string $format = 'Y-m-d'): self
    {
        return new self(DateTimeImmutable::createFromInterface($dt), $format);
    }

    public function asString(): string
    {
        return $this->value->format($this->format);
    }
}
