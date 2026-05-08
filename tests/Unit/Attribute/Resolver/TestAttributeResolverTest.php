<?php

declare(strict_types=1);

namespace Daycry\PHPUnit\Selenium\Tests\Unit\Attribute\Resolver;

use Daycry\PHPUnit\Selenium\Attribute\Resolver\TestAttributeResolver;
use Daycry\PHPUnit\Selenium\Attributes\UseSelenium;
use PHPUnit\Framework\TestCase;

final class TestAttributeResolverTest extends TestCase
{
    public function testReturnsNullWhenNoAttributesPresent(): void
    {
        $resolver = new TestAttributeResolver();

        self::assertNull($resolver->resolve(WithoutAttributes::class, 'doNothing'));
    }

    public function testCollectsClassAndMethodAttributes(): void
    {
        $resolver = new TestAttributeResolver();

        $resolved = $resolver->resolve(WithMethodAttribute::class, 'specificMethod');

        self::assertNotNull($resolved);
        self::assertSame('firefox', $resolved->effective->browser);
        self::assertSame(2, \count($resolved->chain));
    }

    public function testParentClassAttributesAreInherited(): void
    {
        $resolver = new TestAttributeResolver();

        $resolved = $resolver->resolve(ChildWithoutOverride::class, 'someMethod');

        self::assertNotNull($resolved);
        self::assertSame('chrome', $resolved->effective->browser);
    }

    public function testMethodAttributeWinsOverClassAttribute(): void
    {
        $resolver = new TestAttributeResolver();

        $resolved = $resolver->resolve(WithMethodAttribute::class, 'specificMethod');

        self::assertNotNull($resolved);
        self::assertSame('firefox', $resolved->effective->browser);
    }

    public function testRepeatableAttributesMergeRetryAndCapabilities(): void
    {
        $resolver = new TestAttributeResolver();

        $resolved = $resolver->resolve(WithRepeatableAttributes::class, 'someMethod');

        self::assertNotNull($resolved);
        self::assertSame(5, $resolved->effective->retryAttempts);
        self::assertContains('a', $resolved->effective->tags);
        self::assertContains('b', $resolved->effective->tags);
    }

    public function testCacheAvoidsDuplicateReflectionWork(): void
    {
        $resolver = new TestAttributeResolver();

        $first = $resolver->resolve(WithMethodAttribute::class, 'specificMethod');
        $second = $resolver->resolve(WithMethodAttribute::class, 'specificMethod');

        self::assertSame($first, $second);
    }

    public function testResolveOnUnknownMethodReturnsNull(): void
    {
        $resolver = new TestAttributeResolver();

        self::assertNull($resolver->resolve(WithoutAttributes::class, 'doesNotExist'));
    }

    public function testResolveClassWithoutMethod(): void
    {
        $resolver = new TestAttributeResolver();

        $resolved = $resolver->resolveClass(BaseWithChrome::class);

        self::assertNotNull($resolved);
        self::assertSame('chrome', $resolved->effective->browser);
    }
}

final class WithoutAttributes
{
    public function doNothing(): void
    {
    }
}

#[UseSelenium(browser: 'chrome')]
class BaseWithChrome
{
}

#[UseSelenium(browser: 'chrome')]
class WithMethodAttribute
{
    #[UseSelenium(browser: 'firefox')]
    public function specificMethod(): void
    {
    }
}

final class ChildWithoutOverride extends BaseWithChrome
{
    public function someMethod(): void
    {
    }
}

final class WithRepeatableAttributes
{
    #[UseSelenium(retryAttempts: 3, tags: ['a'])]
    #[UseSelenium(retryAttempts: 5, tags: ['b'])]
    public function someMethod(): void
    {
    }
}
