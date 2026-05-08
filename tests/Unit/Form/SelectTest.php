<?php

declare(strict_types=1);

namespace Daycry\PHPUnit\Selenium\Tests\Unit\Form;

use Daycry\PHPUnit\Selenium\Form\Select;
use PHPUnit\Framework\TestCase;

final class SelectTest extends TestCase
{
    public function testByValue(): void
    {
        $option = Select::byValue('ES');

        self::assertSame(Select::BY_VALUE, $option->strategy);
        self::assertSame('ES', $option->value);
    }

    public function testByLabel(): void
    {
        $option = Select::byLabel('Spain');

        self::assertSame(Select::BY_LABEL, $option->strategy);
        self::assertSame('Spain', $option->value);
    }

    public function testByIndex(): void
    {
        $option = Select::byIndex(2);

        self::assertSame(Select::BY_INDEX, $option->strategy);
        self::assertSame(2, $option->value);
    }
}
