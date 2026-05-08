<?php

declare(strict_types=1);

namespace Daycry\PHPUnit\Selenium\Tests\Unit\Attributes;

use Attribute;
use Daycry\PHPUnit\Selenium\Attributes\UseSelenium;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;

final class UseSeleniumTest extends TestCase
{
    public function testAttributeCanBeInstantiated(): void
    {
        self::assertInstanceOf(UseSelenium::class, new UseSelenium());
    }

    public function testAttributeTargetsClassAndMethod(): void
    {
        $reflection = new ReflectionClass(UseSelenium::class);
        $attributes = $reflection->getAttributes(Attribute::class);

        self::assertCount(1, $attributes);

        $flags = $attributes[0]->getArguments()[0] ?? 0;

        self::assertSame(Attribute::TARGET_CLASS, $flags & Attribute::TARGET_CLASS);
        self::assertSame(Attribute::TARGET_METHOD, $flags & Attribute::TARGET_METHOD);
    }

    public function testAttributeIsDiscoverableOnClass(): void
    {
        $reflection = new ReflectionClass(FixtureClassWithAttribute::class);
        $attributes = $reflection->getAttributes(UseSelenium::class);

        self::assertCount(1, $attributes);
        self::assertInstanceOf(UseSelenium::class, $attributes[0]->newInstance());
    }

    public function testAttributeIsDiscoverableOnMethod(): void
    {
        $reflection = new ReflectionMethod(FixtureClassWithAttribute::class, 'methodWithAttribute');
        $attributes = $reflection->getAttributes(UseSelenium::class);

        self::assertCount(1, $attributes);
    }
}

#[UseSelenium]
final class FixtureClassWithAttribute
{
    #[UseSelenium]
    public function methodWithAttribute(): void
    {
    }
}
