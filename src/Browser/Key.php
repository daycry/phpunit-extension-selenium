<?php

declare(strict_types=1);

namespace Daycry\PHPUnit\Selenium\Browser;

use Facebook\WebDriver\WebDriverKeys;

enum Key: string
{
    case Enter = WebDriverKeys::ENTER;
    case Tab = WebDriverKeys::TAB;
    case Escape = WebDriverKeys::ESCAPE;
    case Space = WebDriverKeys::SPACE;
    case Backspace = WebDriverKeys::BACKSPACE;
    case Delete = WebDriverKeys::DELETE;
    case ArrowUp = WebDriverKeys::ARROW_UP;
    case ArrowDown = WebDriverKeys::ARROW_DOWN;
    case ArrowLeft = WebDriverKeys::ARROW_LEFT;
    case ArrowRight = WebDriverKeys::ARROW_RIGHT;
    case Home = WebDriverKeys::HOME;
    case End = WebDriverKeys::END;
    case PageUp = WebDriverKeys::PAGE_UP;
    case PageDown = WebDriverKeys::PAGE_DOWN;
    case Control = WebDriverKeys::CONTROL;
    case Alt = WebDriverKeys::ALT;
    case Shift = WebDriverKeys::SHIFT;
    case Meta = WebDriverKeys::META;
    case F1 = WebDriverKeys::F1;
    case F2 = WebDriverKeys::F2;
    case F3 = WebDriverKeys::F3;
    case F4 = WebDriverKeys::F4;
    case F5 = WebDriverKeys::F5;
}
