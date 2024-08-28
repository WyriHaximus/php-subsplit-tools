<?php

declare(strict_types=1);

namespace WyriHaximus\SubSplitTools;

use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Safe\Exceptions\FilesystemException;

use function dirname;
use function is_file;
use function is_string;
use function rtrim;
use function Safe\file_get_contents;
use function Safe\file_put_contents;
use function Safe\mkdir;
use function str_replace;
use function strlen;
use function substr;
use function WyriHaximus\Twig\render;

use const DIRECTORY_SEPARATOR;

final class Files
{
    /**
     * @param array<mixed> $templateVariables
     */
    public static function setUp(string $templates, string $destination, array $templateVariables): void
    {
        foreach (self::render($templates, $destination, $templateVariables) as $file) {
            try {
                /** @phpstan-ignore-next-line */
                @mkdir(dirname($file->fileName), 0744, true);
            } catch (FilesystemException) {
                // void @ignoreException
            }

            file_put_contents(
                $file->fileName,
                $file->contents,
            );
        }
    }

    /**
     * @param array<mixed> $templateVariables
     *
     * @return iterable<File>
     */
    public static function render(string $templates, string $destination, array $templateVariables): iterable
    {
        foreach (
            new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator(
                    rtrim($templates, '/'),
                    FilesystemIterator::SKIP_DOTS | FilesystemIterator::CURRENT_AS_PATHNAME,
                ),
            ) as $fileName
        ) {
            if (! is_string($fileName)) {
                continue;
            }

            if (! is_file($fileName)) {
                continue;
            }

            $renderedFileName = render(
                rtrim($destination, '/') . DIRECTORY_SEPARATOR . substr($fileName, strlen($templates)),
                $templateVariables,
            );
            do {
                $previousRenderedFileName = $renderedFileName;
                $renderedFileName         = str_replace(['//', '\\\\'], DIRECTORY_SEPARATOR, $renderedFileName);
            } while ($previousRenderedFileName !== $renderedFileName);

            yield new File(
                $renderedFileName,
                render(
                    file_get_contents($fileName),
                    $templateVariables,
                ),
            );
        }
    }
}
