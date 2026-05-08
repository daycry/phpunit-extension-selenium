<?php

declare(strict_types=1);

namespace Daycry\PHPUnit\Selenium\Tests\Unit\Locator;

use Daycry\PHPUnit\Selenium\Locator\Locator;
use Daycry\PHPUnit\Selenium\Locator\LocatorStrategy;
use PHPUnit\Framework\TestCase;

final class LocatorTest extends TestCase
{
    public function testFactoryHelpersBuildLocators(): void
    {
        self::assertSame(LocatorStrategy::Id, Locator::id('foo')->strategy);
        self::assertSame(LocatorStrategy::Css, Locator::css('.x')->strategy);
        self::assertSame(LocatorStrategy::XPath, Locator::xpath('//x')->strategy);
        self::assertSame(LocatorStrategy::Name, Locator::name('email')->strategy);
        self::assertSame(LocatorStrategy::ClassName, Locator::className('btn')->strategy);
        self::assertSame(LocatorStrategy::TagName, Locator::tagName('input')->strategy);
        self::assertSame(LocatorStrategy::LinkText, Locator::linkText('Home')->strategy);
        self::assertSame(LocatorStrategy::PartialLinkText, Locator::partialLinkText('Hom')->strategy);
        self::assertSame(LocatorStrategy::TestId, Locator::testId('login')->strategy);
        self::assertSame(LocatorStrategy::Text, Locator::text('Login')->strategy);
        self::assertSame(LocatorStrategy::Role, Locator::role('button')->strategy);
    }

    public function testValueIsPreserved(): void
    {
        self::assertSame('header', Locator::id('header')->value);
        self::assertSame('//div[@id="x"]', Locator::xpath('//div[@id="x"]')->value);
    }

    public function testDescribeReturnsStrategyEqualsValue(): void
    {
        self::assertSame('id=foo', Locator::id('foo')->describe());
        self::assertSame('css=.btn', Locator::css('.btn')->describe());
    }
}
