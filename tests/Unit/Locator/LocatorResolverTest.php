<?php

declare(strict_types=1);

namespace Daycry\PHPUnit\Selenium\Tests\Unit\Locator;

use Daycry\PHPUnit\Selenium\Locator\Locator;
use Daycry\PHPUnit\Selenium\Locator\LocatorResolver;
use Facebook\WebDriver\WebDriverBy;
use PHPUnit\Framework\TestCase;

final class LocatorResolverTest extends TestCase
{
    public function testIdLocatorMapsToWebDriverByDirectly(): void
    {
        $resolver = new LocatorResolver();
        $by = $resolver->toBy(Locator::id('foo'));

        self::assertSame('id', $by->getMechanism());
        self::assertSame('foo', $by->getValue());
    }

    public function testTestIdLocatorMapsToCssWithCustomAttribute(): void
    {
        $resolver = new LocatorResolver(testIdAttribute: 'data-test');
        $by = $resolver->toBy(Locator::testId('submit'));

        self::assertSame('css selector', $by->getMechanism());
        self::assertSame('[data-test="submit"]', $by->getValue());
    }

    public function testRoleLocatorMapsToCssAttributeSelector(): void
    {
        $resolver = new LocatorResolver();
        $by = $resolver->toBy(Locator::role('button'));

        self::assertSame('css selector', $by->getMechanism());
        self::assertSame('[role="button"]', $by->getValue());
    }

    public function testTextLocatorMapsToXpathWithLiteralEscaping(): void
    {
        $resolver = new LocatorResolver();
        $by = $resolver->toBy(Locator::text("It's me"));

        self::assertSame('xpath', $by->getMechanism());
        self::assertStringContainsString("It's me", $by->getValue());
    }

    public function testTextLocatorWithBothQuotesUsesConcat(): void
    {
        $resolver = new LocatorResolver();
        $by = $resolver->toBy(Locator::text(/* both quotes */ 'He said "It\'s ok"'));

        self::assertStringStartsWith('//*[normalize-space(text())=concat(', $by->getValue());
    }

    public function testCssLocatorPassesThrough(): void
    {
        $resolver = new LocatorResolver();
        $by = $resolver->toBy(Locator::css('.foo > .bar'));

        self::assertSame('css selector', $by->getMechanism());
        self::assertSame('.foo > .bar', $by->getValue());
    }

    public function testAllStrategiesProduceWebDriverBy(): void
    {
        $resolver = new LocatorResolver();
        $locators = [
            Locator::id('a'),
            Locator::css('.a'),
            Locator::xpath('//a'),
            Locator::name('a'),
            Locator::className('a'),
            Locator::tagName('a'),
            Locator::linkText('a'),
            Locator::partialLinkText('a'),
            Locator::testId('a'),
            Locator::text('a'),
            Locator::role('a'),
        ];

        foreach ($locators as $locator) {
            self::assertInstanceOf(WebDriverBy::class, $resolver->toBy($locator));
        }
    }
}
