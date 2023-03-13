<?php

declare(strict_types=1);

/*
 * This file is part of the Doctrine Behavioral Extensions package.
 * (c) Gediminas Morkevicius <gediminas.morkevicius@gmail.com> http://www.gediminasm.org
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Gedmo\Tests\Uploadable\Mapping;

use Doctrine\ORM\Mapping\ClassMetadata;
use Gedmo\Exception\InvalidMappingException;
use Gedmo\Exception\UploadableInvalidPathException;
use Gedmo\Uploadable\FilenameGenerator\FilenameGeneratorSha1;
use Gedmo\Uploadable\Mapping\Validator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * These are tests for the Mapping Validator of the Uploadable behavior
 *
 * @author Gustavo Falco <comfortablynumb84@gmail.com>
 * @author Gediminas Morkevicius <gediminas.morkevicius@gmail.com>
 */
final class ValidatorTest extends TestCase
{
    /**
     * @var ClassMetadata&MockObject
     */
    protected $meta;

    protected function setUp(): void
    {
        $this->meta = $this->getMockBuilder(ClassMetadata::class)
            ->setConstructorArgs(['', null])
            ->getMock();

        Validator::$enableMimeTypesConfigException = false;
    }

    protected function tearDown(): void
    {
        Validator::$enableMimeTypesConfigException = true;
    }

    public function testValidateFieldIfFieldIsNotOfAValidTypeThrowException(): void
    {
        $this->expectException(InvalidMappingException::class);
        $this->meta->expects(static::once())
            ->method('getFieldMapping')
            ->willReturn(['type' => 'someType']);

        Validator::validateField(
            $this->meta,
            'someField',
            Validator::UPLOADABLE_FILE_MIME_TYPE,
            Validator::$validFileMimeTypeTypes
        );
    }

    public function testValidatePathIfPathIsNotAStringOrIsAnEmptyStringThrowException(): void
    {
        $this->expectException(UploadableInvalidPathException::class);
        Validator::validatePath('');
    }

    public function testValidatePathCreatesNewDirectoryWhenItNotExists(): void
    {
        $dir = TESTS_TEMP_DIR.'/new/directory-12312432423';
        Validator::validatePath($dir);
        static::assertDirectoryExists($dir);
        rmdir($dir);
        rmdir(dirname($dir));
    }

    public function testValidateConfigurationIfNeitherFilePathFieldNorFileNameFieldIsNotDefinedThrowException(): void
    {
        $this->expectException(InvalidMappingException::class);
        $config = ['filePathField' => null, 'fileNameField' => null];

        Validator::validateConfiguration($this->meta, $config);
    }

    public function testValidateConfigurationIfPathMethodIsNotAValidMethodThrowException(): void
    {
        $this->expectException(InvalidMappingException::class);
        $this->meta->expects(static::once())
            ->method('getReflectionClass')
            ->willReturn(new \ReflectionClass(new FakeEntity()));

        $config = ['filePathField' => 'someField', 'pathMethod' => 'invalidMethod'];

        Validator::validateConfiguration(
            $this->meta,
            $config
        );
    }

    public function testValidateConfigurationIfCallbackMethodIsNotAValidMethodThrowException(): void
    {
        $this->expectException(InvalidMappingException::class);
        $this->meta->expects(static::once())
            ->method('getReflectionClass')
            ->willReturn(new \ReflectionClass(new FakeEntity()));

        $config = ['filePathField' => 'someField', 'pathMethod' => '', 'callback' => 'invalidMethod'];

        Validator::validateConfiguration(
            $this->meta,
            $config
        );
    }

    public function testValidateConfigurationIfFilenameGeneratorValueIsNotValidThrowException(): void
    {
        $this->expectException(InvalidMappingException::class);
        $this->meta->expects(static::once())
            ->method('getReflectionClass')
            ->willReturn(new \ReflectionClass(new FakeEntity()));
        $this->meta
            ->method('getFieldMapping')
            ->willReturn(['type' => 'someType']);

        $config = [
            'fileMimeTypeField' => null,
            'fileSizeField' => null,
            'fileNameField' => null,
            'filePathField' => 'someField',
            'pathMethod' => null,
            'callback' => null,
            'filenameGenerator' => 'invalidClass',
            'maxSize' => 0.0,
            'allowedTypes' => '',
            'disallowedTypes' => '',
        ];

        Validator::validateConfiguration(
            $this->meta,
            $config
        );
    }

    public function testValidateConfigurationIfFilenameGeneratorValueIsValidButDoesntImplementNeededInterfaceThrowException(): void
    {
        $this->expectException(InvalidMappingException::class);
        $this->meta->expects(static::once())
            ->method('getReflectionClass')
            ->willReturn(new \ReflectionClass(new FakeEntity()));
        $this->meta
            ->method('getFieldMapping')
            ->willReturn(['type' => 'someType']);

        $config = [
            'fileMimeTypeField' => null,
            'fileSizeField' => null,
            'fileNameField' => null,
            'filePathField' => 'someField',
            'pathMethod' => null,
            'callback' => null,
            'filenameGenerator' => 'DateTime',
            'maxSize' => 0.0,
            'allowedTypes' => '',
            'disallowedTypes' => '',
        ];

        Validator::validateConfiguration(
            $this->meta,
            $config
        );
    }

    public function testValidateConfigurationIfFilenameGeneratorValueIsValidThenDontThrowException(): void
    {
        $this->meta->expects(static::once())
            ->method('getReflectionClass')
            ->willReturn(new \ReflectionClass(new FakeEntity()));
        $this->meta
            ->method('getFieldMapping')
            ->willReturn(['type' => 'string']);

        $config = [
            'fileMimeTypeField' => null,
            'fileSizeField' => null,
            'fileNameField' => null,
            'filePathField' => 'someField',
            'pathMethod' => null,
            'callback' => null,
            'filenameGenerator' => 'SHA1',
            'maxSize' => 0.0,
            'allowedTypes' => '',
            'disallowedTypes' => '',
        ];

        Validator::validateConfiguration(
            $this->meta,
            $config
        );
    }

    public function testValidateConfigurationIfFilenameGeneratorValueIsAValidClassThenDontThrowException(): void
    {
        $this->meta->expects(static::once())
            ->method('getReflectionClass')
            ->willReturn(new \ReflectionClass(new FakeEntity()));
        $this->meta
            ->method('getFieldMapping')
            ->willReturn(['type' => 'string']);

        $config = [
            'fileMimeTypeField' => null,
            'fileSizeField' => null,
            'fileNameField' => null,
            'filePathField' => 'someField',
            'pathMethod' => null,
            'callback' => null,
            'filenameGenerator' => FilenameGeneratorSha1::class,
            'maxSize' => 0.0,
            'allowedTypes' => '',
            'disallowedTypes' => '',
        ];

        Validator::validateConfiguration(
            $this->meta,
            $config
        );
    }

    public function testValidateConfigurationIfMaxSizeIsLessThanZeroThenThrowException(): void
    {
        $this->expectException(InvalidMappingException::class);
        $this->meta->expects(static::once())
            ->method('getReflectionClass')
            ->willReturn(new \ReflectionClass(new FakeEntity()));

        $config = [
            'fileMimeTypeField' => 'someField',
            'filePathField' => 'someField',
            'fileSizeField' => null,
            'pathMethod' => null,
            'callback' => null,
            'maxSize' => -123,
            'allowedTypes' => '',
            'disallowedTypes' => '',
        ];

        Validator::validateConfiguration(
            $this->meta,
            $config
        );
    }

    public function testValidateConfigurationIfAllowedTypesAndDisallowedTypesAreSetThenThrowException(): void
    {
        $this->expectException(InvalidMappingException::class);
        $this->meta->expects(static::once())
            ->method('getReflectionClass')
            ->willReturn(new \ReflectionClass(new FakeEntity()));

        Validator::$enableMimeTypesConfigException = true;

        $config = [
            'fileMimeTypeField' => 'someField',
            'filePathField' => 'someField',
            'fileSizeField' => null,
            'pathMethod' => null,
            'callback' => null,
            'maxSize' => 0.0,
            'allowedTypes' => 'text/plain',
            'disallowedTypes' => 'text/css',
        ];

        Validator::validateConfiguration(
            $this->meta,
            $config
        );
    }
}

class FakeEntity
{
}
