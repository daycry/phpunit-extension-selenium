<?php

declare(strict_types=1);

namespace Daycry\PHPUnit\Selenium\Tests\Unit\Form;

use DateTimeImmutable;
use Daycry\PHPUnit\Selenium\Form\Date as FormDate;
use PHPUnit\Framework\TestCase;

final class DateTest extends TestCase
{
    public function testOfDefaultsToIsoFormat(): void
    {
        self::assertSame('1990-01-01', FormDate::of('1990-01-01')->asString());
    }

    public function testCustomFormat(): void
    {
        self::assertSame('01/01/1990', FormDate::of('1990-01-01', 'd/m/Y')->asString());
    }

    public function testFromDateTime(): void
    {
        $dt = new DateTimeImmutable('2026-05-08');
        self::assertSame('2026-05-08', FormDate::fromDateTime($dt)->asString());
    }
}
