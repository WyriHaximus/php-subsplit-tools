<?php

declare(strict_types=1);

namespace WyriHaximus\Tests\SubSplitTools;

use WyriHaximus\SubSplitTools\Files;
use WyriHaximus\TestUtilities\TestCase;

use function time;

use const DIRECTORY_SEPARATOR;

final class FilesTest extends TestCase
{
    /**
     * @test
     */
    public function allFilesGotMovedOver(): void
    {
        Files::setUp(
            __DIR__ . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR,
            $this->getTmpDir() . DIRECTORY_SEPARATOR,
            [],
        );

        self::assertFileExists($this->getTmpDir() . 'root.txt');
        self::assertFileExists($this->getTmpDir() . 'a/first.level.txt');
        self::assertFileExists($this->getTmpDir() . 'a/b/second.level.txt');
        self::assertFileExists($this->getTmpDir() . 'a/b/c/third.level.txt');
    }

    /**
     * @test
     */
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

        self::assertArrayHasKey($this->getTmpDir() . $time . '/root.txt', $files);
        self::assertArrayHasKey($this->getTmpDir() . $time . '/a/first.level.txt', $files);
        self::assertArrayHasKey($this->getTmpDir() . $time . '/a/b/second.level.txt', $files);
        self::assertArrayHasKey($this->getTmpDir() . $time . '/a/b/c/third.level.txt', $files);
    }
}
