<?php

declare(strict_types=1);

namespace WyriHaximus\Tests\SubSplitTools;

use PHPUnit\Framework\Attributes\Test;
use ReflectionMethod;
use ReflectionProperty;
use WyriHaximus\SubSplitTools\File;
use WyriHaximus\SubSplitTools\Files;
use WyriHaximus\TestUtilities\TestCase;

use function array_key_first;
use function file_get_contents;
use function file_put_contents;
use function mkdir;
use function str_ends_with;
use function time;

use const DIRECTORY_SEPARATOR;

final class FilesTest extends TestCase
{
    #[Test]
    public function allFilesGotMovedOver(): void
    {
        Files::setUp(
            __DIR__ . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR,
            $this->getTmpDir() . DIRECTORY_SEPARATOR,
            [],
        );

        self::assertFileExists($this->getTmpDir() . 'root.txt');
        self::assertFileExists($this->getTmpDir() . 'a' . DIRECTORY_SEPARATOR . 'first.level.txt');
        self::assertFileExists($this->getTmpDir() . 'a' . DIRECTORY_SEPARATOR . 'b' . DIRECTORY_SEPARATOR . 'second.level.txt');
        self::assertFileExists($this->getTmpDir() . 'a' . DIRECTORY_SEPARATOR . 'b' . DIRECTORY_SEPARATOR . 'c' . DIRECTORY_SEPARATOR . 'third.level.txt');
    }

    #[Test]
    public function renderTemplatedPath(): void
    {
        $time  = time();
        $files = [];

        foreach (
            Files::render(
                __DIR__ . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR,
                $this->getTmpDir() . DIRECTORY_SEPARATOR . '//{{ time }}\\\\' . DIRECTORY_SEPARATOR,
                ['time' => $time],
            ) as $file
        ) {
            $files[$file->fileName] = $file;
        }

        self::assertArrayHasKey($this->getTmpDir() . $time . DIRECTORY_SEPARATOR . 'root.txt', $files);
        self::assertArrayHasKey($this->getTmpDir() . $time . DIRECTORY_SEPARATOR . 'a' . DIRECTORY_SEPARATOR . 'first.level.txt', $files);
        self::assertArrayHasKey($this->getTmpDir() . $time . DIRECTORY_SEPARATOR . 'a' . DIRECTORY_SEPARATOR . 'b' . DIRECTORY_SEPARATOR . 'second.level.txt', $files);
        self::assertArrayHasKey($this->getTmpDir() . $time . DIRECTORY_SEPARATOR . 'a' . DIRECTORY_SEPARATOR . 'b' . DIRECTORY_SEPARATOR . 'c' . DIRECTORY_SEPARATOR . 'third.level.txt', $files);
    }

    #[Test]
    public function renderSkipsUnreadableFile(): void
    {
        $templates = $this->getTmpDir() . 'templates' . DIRECTORY_SEPARATOR;
        mkdir($templates, 0755, true);
        file_put_contents($templates . 'readable.txt', 'readable');
        file_put_contents($templates . 'unreadable.txt', 'unreadable');

        $destination = $this->getTmpDir() . 'destination' . DIRECTORY_SEPARATOR;

        $readTemplateFileContents = new ReflectionMethod(Files::class, 'readTemplateFileContents');
        self::assertSame('readable', $readTemplateFileContents->invoke(null, $templates . 'readable.txt'));

        $readOverride = static function (string $fileName): string|false {
            if (str_ends_with($fileName, 'unreadable.txt')) {
                return false;
            }

            return (string) file_get_contents($fileName);
        };

        $files = $this->withReadTemplateFileContentsOverride(
            $readOverride,
            fn (): array => $this->collectRenderedFiles($templates, $destination),
        );

        self::assertCount(1, $files);
        self::assertSame('readable', $files[array_key_first($files)]->contents);
    }

    /** @return array<string, File> */
    private function collectRenderedFiles(string $templates, string $destination): array
    {
        $files = [];

        foreach (Files::render($templates, $destination, []) as $file) {
            $files[$file->fileName] = $file;
        }

        return $files;
    }

    /**
     * @param callable(string): (string|false) $readTemplateFileContents
     * @param callable(): array<string, File>  $collectRenderedFiles
     *
     * @return array<string, File>
     */
    private function withReadTemplateFileContentsOverride(
        callable $readTemplateFileContents,
        callable $collectRenderedFiles,
    ): array {
        $override = new ReflectionProperty(Files::class, 'readTemplateFileContentsOverride');
        $override->setValue(null, $readTemplateFileContents);

        try {
            return $collectRenderedFiles();
        } finally {
            $override->setValue(null, null);
        }
    }
}
