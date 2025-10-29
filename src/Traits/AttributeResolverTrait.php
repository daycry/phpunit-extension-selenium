<?php

declare(strict_types=1);

namespace Daycry\PHPUnit\Selenium\Traits;

use Daycry\PHPUnit\Selenium\Attributes\UseSelenium;
use Exception;
use PHPUnit\Event\Code\Test;
use PHPUnit\Event\Code\TestMethod;
use PHPUnit\Event\Event;
use ReflectionClass;
use ReflectionMethod;

trait AttributeResolverTrait
{
    private function needsSelenium(Test|Event $test): bool
    {
        return $this->getAttribute($test) !== null;
    }

    private function getAttribute(Test|Event $test): ?UseSelenium
    {
        $reflection = new ReflectionClass($test);
        $class = $reflection->getProperty('className')->getValue($test);

        $method = $test instanceof TestMethod ? $test->methodName() : $test->name();

        try {
            $method = new ReflectionMethod($class, $method);
        } catch (Exception) {
            return null;
        }

        $attributes = $method->getAttributes(UseSelenium::class);

        if ($attributes !== []) {
            return $attributes[0]->newInstance();
        } else {
            $class = $method->getDeclaringClass();
            $attributes = $class->getAttributes(UseSelenium::class);

            if ($attributes) {
                return $attributes[0]->newInstance();
            }

            return null;
        }

    }
}
