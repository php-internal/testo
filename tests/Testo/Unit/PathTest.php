<?php

declare(strict_types=1);

namespace Tests\Testo\Unit;

use Testo\Assert;
use Testo\Common\Path;
use Testo\Sample\DataProvider;

#[\Testo\Attribute\CoversClass(Path::class)]
final class PathTest
{
    public static function providePathsForAbsoluteDetection(): \Generator
    {
        yield 'windows absolute path' => ['C:/Users/test', true];
        yield 'windows drive letter' => ['C:', true];
        yield 'windows relative path' => ['Users/test', false];
        yield 'windows implicit relative' => ['./test', false];
        yield 'unix absolute path' => ['/home/user', true];
        yield 'unix relative path' => ['home/user', false];
        yield 'unix implicit relative' => ['./test', false];
        yield 'dot path' => ['.', false];
        yield 'double dot path' => ['..', false];
    }

    public static function providePathsForParent(): \Generator
    {
        yield ['.', '..'];
        yield ['..', '../..'];
        yield ['path/to/..', '.'];
        yield ['/home', '/.'];
        yield ['C:/Users', 'C:/.'];
        yield ['C:/.', 'C:/.'];
        yield ['filename.txt', '.'];
        yield ['some/path/file.txt', 'some/path'];
    }

    public function testCreateReturnsPathInstance(): void
    {
        // Arrange & Act
        $path = Path::create('test/path');

        // Assert
        Assert::InstanceOf(Path::class, $path);
    }

    public function testCreateWithEmptyPathReturnsCurrentDirectory(): void
    {
        // Arrange & Act
        $path = Path::create('');

        // Assert
        Assert::same('.', (string) $path);
    }

    public function testCreateNormalizesDirectorySeparators(): void
    {
        // Arrange & Act
        $path = Path::create('test\\path/mixed/separators\\here');

        // Assert
        Assert::same('test/path/mixed/separators/here', (string) $path);
    }

    public function testCreateRemovesMultipleSeparators(): void
    {
        // Arrange & Act
        $path = Path::create('test//path///extra//separators');

        // Assert
        Assert::same('test/path/extra/separators', (string) $path);
    }

    public function testCreateResolvesCurrentDirectorySegments(): void
    {
        // Arrange & Act
        $path = Path::create('test/./path/./current');

        // Assert
        Assert::same('test/path/current', (string) $path);
    }

    public function testCreateResolvesParentDirectorySegments(): void
    {
        // Arrange & Act
        $path = Path::create('test/parent/../path');

        // Assert
        Assert::same('test/path', (string) $path);
    }

    public function testCreateThrowsExceptionForInvalidParentNavigation(): void
    {
        // Arrange & Assert
        Assert::exception(\LogicException::class);
        // ->withMessage('Cannot go up from root');

        // Act
        Path::create('/test/../..');
    }

    public function testJoinPathComponents(): void
    {
        // Arrange
        $path = Path::create('base/path');

        // Act
        $result = $path->join('additional', 'components');

        // Assert
        Assert::same('base/path/additional/components', (string) $result);
    }

    public function testJoinWithEmptyComponentsIgnoresThem(): void
    {
        // Arrange
        $path = Path::create('base/path');

        // Act
        $result = $path->join('', 'component', '');

        // Assert
        Assert::same('base/path/component', (string) $result);
    }

    public function testJoinWithPathObjects(): void
    {
        // Arrange
        $path = Path::create('base/path');
        $additionalPath = Path::create('additional/path');

        // Assert (prepare for expected exception)
        Assert::exception(\LogicException::class);
        // ->withMessage('Joining an absolute path is not allowed');

        // Act
        // Using an absolute Path object which should throw
        $path->join($additionalPath->absolute());
    }

    public function testJoinWithRelativePathObjects(): void
    {
        // Arrange
        $path = Path::create('base/path');
        $additionalPath = Path::create('additional/path');

        // Act
        $result = $path->join($additionalPath);

        // Assert
        Assert::same('base/path/additional/path', (string) $result);
    }

    public function testJoinWithAbsolutePathString(): void
    {
        // Arrange
        $path = Path::create('base/path');

        // Assert (prepare for expected exception)
        Assert::exception(\LogicException::class);
        // ->withMessage('Joining an absolute path is not allowed');

        // Act
        $path->join('/absolute/path');
    }

    public function testName(): void
    {
        // Arrange
        $path = Path::create('some/path/file.txt');

        // Act
        $name = $path->name();

        // Assert
        Assert::same('file.txt', $name);
    }

    public function testNameWithNoDirectoryComponents(): void
    {
        // Arrange
        $path = Path::create('file.txt');

        // Act
        $name = $path->name();

        // Assert
        Assert::same('file.txt', $name);
    }

    public function testStem(): void
    {
        // Arrange
        $path = Path::create('some/path/file.txt');

        // Act
        $stem = $path->stem();

        // Assert
        Assert::same('file', $stem);
    }

    public function testStemWithNoExtension(): void
    {
        // Arrange
        $path = Path::create('some/path/file');

        // Act
        $stem = $path->stem();

        // Assert
        Assert::same('file', $stem);
    }

    public function testStemWithMultipleDots(): void
    {
        // Arrange
        $path = Path::create('some/path/file.config.json');

        // Act
        $stem = $path->stem();

        // Assert
        Assert::same('file.config', $stem);
    }

    public function testStemWithHiddenFile(): void
    {
        // Arrange
        $path = Path::create('some/path/.hidden');

        // Act
        $stem = $path->stem();

        // Assert
        Assert::same('.hidden', $stem);
    }

    public function testExtension(): void
    {
        // Arrange
        $path = Path::create('some/path/file.txt');

        // Act
        $extension = $path->extension();

        // Assert
        Assert::same('txt', $extension);
    }

    public function testExtensionWithMultipleDots(): void
    {
        // Arrange
        $path = Path::create('some/path/file.config.json');

        // Act
        $extension = $path->extension();

        // Assert
        Assert::same('json', $extension);
    }

    public function testExtensionWithNoExtension(): void
    {
        // Arrange
        $path = Path::create('some/path/file');

        // Act
        $extension = $path->extension();

        // Assert
        Assert::same('', $extension);
    }

    public function testExtensionWithHiddenFile(): void
    {
        // Arrange
        $path = Path::create('some/path/.hidden');

        // Act
        $extension = $path->extension();

        // Assert
        Assert::same('hidden', $extension);
    }

    #[DataProvider('providePathsForParent')]
    public function testParent(string $inputPath, string $expectedParent): void
    {
        // Arrange
        $path = Path::create($inputPath);

        // Act
        $parent = $path->parent();

        // Assert
        Assert::same($expectedParent, (string) $parent);
    }

    #[DataProvider('providePathsForAbsoluteDetection')]
    public function testIsAbsolute(string $pathString, bool $expected): void
    {
        // Arrange
        $path = Path::create($pathString);

        // Act
        $isAbsolute = $path->isAbsolute();

        // Assert
        Assert::same($expected, $isAbsolute, "Path '$pathString' should be " . ($expected ? 'absolute' : 'relative'));
    }

    public function testIsRelative(): void
    {
        // Arrange
        $absolutePath = DIRECTORY_SEPARATOR === '\\'
            ? Path::create('C:/Users/test')
            : Path::create('/home/user');

        $relativePath = Path::create('relative/path');

        // Act & Assert
        Assert::false($absolutePath->isRelative());
        Assert::true($relativePath->isRelative());
    }

    /**
     * This test uses real filesystem access to check if a path exists.
     * It creates a temporary file and checks its existence.
     */
    public function testExists(): void
    {
        // Arrange
        $tempFile = \tempnam(\sys_get_temp_dir(), 'path_test_');
        Assert::true(\is_string($tempFile), 'Failed to create temp file');

        $path = Path::create($tempFile);
        $nonExistingPath = Path::create('non/existing/path/file.txt');

        // Act & Assert
        try {
            Assert::true($path->exists());
            Assert::false($nonExistingPath->exists());
        } finally {
            // Clean up
            @\unlink($tempFile);
        }
    }

    /**
     * Note: This test might have limitations depending on the environment.
     * It checks the expected behavior of isDir without requiring an actual directory to exist.
     */
    public function testIsDir(): void
    {
        // Arrange
        $currentDirPath = Path::create('.');
        $parentDirPath = Path::create('..');
        $filePath = Path::create('file.txt');

        // Act & Assert
        Assert::true($currentDirPath->isDir());
        Assert::true($parentDirPath->isDir());
        Assert::false($filePath->isDir());
    }

    /**
     * Note: This test might have limitations depending on the environment.
     * It checks the expected behavior of isFile without requiring an actual file to exist.
     */
    public function testIsFile(): void
    {
        // Arrange
        $currentDirPath = Path::create('.');
        $parentDirPath = Path::create('..');
        $filePath = Path::create('file.txt');

        // Create a temporary file to test with
        $tempFile = \tempnam(\sys_get_temp_dir(), 'path_test_');
        Assert::true(\is_string($tempFile), 'Failed to create temp file');
        $realFilePath = Path::create($tempFile);

        // Act & Assert
        try {
            Assert::false($currentDirPath->isFile());
            Assert::false($parentDirPath->isFile());
            Assert::false($filePath->isFile()); // Doesn't exist yet
            Assert::true($realFilePath->isFile(), "Temporary file should be a file `$realFilePath`");
        } finally {
            // Clean up
            @\unlink($tempFile);
        }
    }

    public function testAbsoluteForAlreadyAbsolutePath(): void
    {
        // Arrange
        $absolutePath = DIRECTORY_SEPARATOR === '\\'
            ? Path::create('C:/Users/test')
            : Path::create('/home/user');

        // Act
        $result = $absolutePath->absolute();

        // Assert
        Assert::same((string) $absolutePath, (string) $result);
    }

    public function testAbsoluteForRelativePath(): void
    {
        // Arrange
        $relativePath = Path::create('relative/path');

        // Skip this test if we can't get cwd
        $cwd = \getcwd();
        if ($cwd === false) {
            // todo
            // self::markTestSkipped('Cannot get current working directory');
        }

        $expected = Path::create($cwd . DIRECTORY_SEPARATOR . 'relative/path');

        // Act
        $result = $relativePath->absolute();

        // Assert
        Assert::same((string) $expected, (string) $result);
    }

    public function testCreateWindowsTmpFile(): void
    {
        $path = Path::create('C:\Users\roxbl\AppData\Local\Temp\patB6E7.tmp');

        Assert::same('C:/Users/roxbl/AppData/Local/Temp/patB6E7.tmp', (string) $path);
    }

    public function testToString(): void
    {
        // Arrange
        $pathString = 'some/path/file.txt';
        $path = Path::create($pathString);

        // Act
        $result = (string) $path;

        // Assert
        Assert::same('some/path/file.txt', $result);
    }
}
