<?php

declare(strict_types=1);

namespace Daycry\PHPUnit\Selenium\Tests\Unit\Container;

use Daycry\PHPUnit\Selenium\Container\Exception\NotFoundException;
use Daycry\PHPUnit\Selenium\Container\ServiceContainer;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use stdClass;

final class ServiceContainerTest extends TestCase
{
    public function testImplementsPsr11(): void
    {
        self::assertInstanceOf(ContainerInterface::class, new ServiceContainer());
    }

    public function testFactoryIsLazyAndMemoised(): void
    {
        $container = new ServiceContainer();
        $invocations = 0;
        $container->set('clock', static function () use (&$invocations): stdClass {
            ++$invocations;
            return new stdClass();
        });

        self::assertSame(0, $invocations);

        $first = $container->get('clock');
        $second = $container->get('clock');

        self::assertSame(1, $invocations);
        self::assertSame($first, $second);
    }

    public function testInstanceTakesPrecedenceOverFactory(): void
    {
        $container = new ServiceContainer();
        $container->set('x', static fn (): string => 'factory');
        $container->instance('x', 'instance');

        self::assertSame('instance', $container->get('x'));
    }

    public function testHasReportsRegistration(): void
    {
        $container = new ServiceContainer();
        self::assertFalse($container->has('missing'));

        $container->instance('missing', 1);
        self::assertTrue($container->has('missing'));
    }

    public function testGetMissingThrowsPsr11NotFound(): void
    {
        $container = new ServiceContainer();

        try {
            $container->get('nope');
            self::fail('Expected NotFoundException');
        } catch (NotFoundException $e) {
            self::assertInstanceOf(NotFoundExceptionInterface::class, $e);
            self::assertStringContainsString('"nope"', $e->getMessage());
        }
    }
}
