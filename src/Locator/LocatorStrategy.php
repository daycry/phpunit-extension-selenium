<?php

declare(strict_types=1);

namespace Daycry\PHPUnit\Selenium\Locator;

enum LocatorStrategy: string
{
    case Id = 'id';
    case Css = 'css';
    case XPath = 'xpath';
    case Name = 'name';
    case ClassName = 'className';
    case TagName = 'tagName';
    case LinkText = 'linkText';
    case PartialLinkText = 'partialLinkText';
    case TestId = 'testId';
    case Text = 'text';
    case Role = 'role';
}
