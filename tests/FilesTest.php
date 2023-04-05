<?php

declare(strict_types=1);

namespace WyriHaximus\Tests\SubSplitTools;

use WyriHaximus\SubSplitTools\Files;
use WyriHaximus\TestUtilities\TestCase;

use const DIRECTORY_SEPARATOR;

final class FilesTest extends TestCase
{
    /**
     * @test
     */
    public function allFilesGotMovedOver(): void
    {
        Files::setUp(
            __DIR__ . DIRECTORY_SEPARATOR . 'templates',
            $this->getTmpDir(),
            [],
        );

        self::assertFileExists($this->getTmpDir() . 'root.txt');
        self::assertFileExists($this->getTmpDir() . 'a/first.level.txt');
        self::assertFileExists($this->getTmpDir() . 'a/b/second.level.txt');
        self::assertFileExists($this->getTmpDir() . 'a/b/c/third.level.txt');
    }
}
