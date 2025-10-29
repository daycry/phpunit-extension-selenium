<?php

declare(strict_types=1);

namespace Daycry\PHPUnit\Selenium\Subscribers;

use Daycry\PHPUnit\Selenium\Traits\AttributeResolverTrait;
use PHPUnit\Event\Test\Prepared;
use PHPUnit\Event\Test\PreparedSubscriber;
use Daycry\PHPUnit\Selenium\Subscribers\ConfigurationSubscriber;
use ReflectionObject;

class StartSeleniumSubscriber implements PreparedSubscriber
{
    use AttributeResolverTrait;

    public function notify(Prepared $event): void
    {
        $test = $event->test();

        if($this->needsSelenium($test)){
            $reflection = new ReflectionObject($test);
            $class = $reflection->getProperty('className')->getValue($test);

            if ($reflection->hasProperty('driver')) {
                dd('has driver property');
            }
            dd($class);
            
        }

        /*if ($this->needsSelenium($test)) {
            $object = null;
            $reflection = new \ReflectionObject($test);

            dd($reflection->getProperties());
            // Buscar propiedades comunes donde PHPUnit podría guardar el objeto de test
            foreach (["test", "instance"] as $propertyName) {
                if ($reflection->hasProperty($propertyName)) {
                    $property = $reflection->getProperty($propertyName);
                    $property->setAccessible(true);
                    $object = $property->getValue($test);
                    if (is_object($object)) {
                        break;
                    }
                }
            }

            dd($object);
            if ($object !== null) {
                $objectReflection = new \ReflectionObject($object);
                if ($objectReflection->hasProperty('driver')) {
                    $driverProperty = $objectReflection->getProperty('driver');
                    if ($driverProperty->isPublic()) {
                        $driverProperty->setValue($object, ConfigurationSubscriber::getDriver());
                    }
                }
            }
        }*/
    }
}