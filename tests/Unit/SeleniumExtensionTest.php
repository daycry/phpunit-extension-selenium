<?php

declare(strict_types=1);

namespace Daycry\PHPUnit\Selenium\Tests\Unit;

use Daycry\PHPUnit\Selenium\SeleniumExtension;
use PHPUnit\Framework\TestCase;
use PHPUnit\Runner\Extension\Extension;
use ReflectionClass;

final class SeleniumExtensionTest extends TestCase
{
    public function testExtensionImplementsExtensionInterface(): void
    {
        self::assertInstanceOf(Extension::class, new SeleniumExtension());
    }

    public function testExtensionExposesBootstrapMethod(): void
    {
        $reflection = new ReflectionClass(SeleniumExtension::class);

        self::assertTrue($reflection->hasMethod('bootstrap'));
        self::assertTrue($reflection->getMethod('bootstrap')->isPublic());
    }
}
