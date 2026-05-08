<?php

declare(strict_types=1);

namespace Daycry\PHPUnit\Selenium\Container\Exception;

use Daycry\PHPUnit\Selenium\Exception\SeleniumException;
use Psr\Container\NotFoundExceptionInterface;

final class NotFoundException extends SeleniumException implements NotFoundExceptionInterface
{
}
