<?php

declare(strict_types=1);

namespace Daycry\PHPUnit\Selenium\Tests\Unit\Form;

use Daycry\PHPUnit\Selenium\Exception\ConfigurationException;
use Daycry\PHPUnit\Selenium\Form\Upload;
use PHPUnit\Framework\TestCase;

final class UploadTest extends TestCase
{
    public function testFactoryRequiresExistingFile(): void
    {
        $this->expectException(ConfigurationException::class);
        Upload::file('/path/that/does/not/exist.png');
    }

    public function testFactoryReturnsValueObjectWithPath(): void
    {
        $tmp = tempnam(sys_get_temp_dir(), 'upload-test-');
        self::assertNotFalse($tmp);

        try {
            $upload = Upload::file($tmp);
            self::assertSame($tmp, $upload->path);
        } finally {
            @unlink($tmp);
        }
    }
}
