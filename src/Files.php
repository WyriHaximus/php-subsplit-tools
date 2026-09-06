<?php

declare(strict_types=1);

namespace WyriHaximus\SubSplitTools;

use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

use function dirname;
use function file_get_contents;
use function file_put_contents;
use function is_dir;
use function is_file;
use function mkdir;
use function rtrim;
use function str_replace;
use function strlen;
use function substr;
use function WyriHaximus\Twig\render;

use const DIRECTORY_SEPARATOR;

/** @api */
final class Files
{
    /**
     * @var callable(string): (string|false)|null
     * @phpstan-ignore property.onlyRead, property.unusedType
     */
    private static $readTemplateFileContentsOverride;

    /** @param array<string, mixed> $templateVariables */
    public static function setUp(string $templates, string $destination, array $templateVariables): void
    {
        foreach (self::render($templates, $destination, $templateVariables) as $file) {
            $directory = dirname($file->fileName);
            if (! is_dir($directory)) {
                mkdir($directory, 0744, true);
            }

            file_put_contents(
                $file->fileName,
                $file->contents,
            );
        }
    }

    /**
     * @param array<string, mixed> $templateVariables
     *
     * @return iterable<File>
     */
    public static function render(string $templates, string $destination, array $templateVariables): iterable
    {
        $templateRoot = rtrim($templates, '/\\');

        /** @var SplFileInfo $node */
        foreach (
            new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator(
                    $templateRoot,
                    FilesystemIterator::SKIP_DOTS,
                ),
                RecursiveIteratorIterator::SELF_FIRST,
            ) as $node
        ) {
            $fileName = $node->getPathname();
            if (! is_file($fileName)) {
                continue;
            }

            $relativePath = substr($fileName, strlen($templateRoot) + 1);

            $renderedFileName = render(
                rtrim($destination, '/\\') . DIRECTORY_SEPARATOR . $relativePath,
                $templateVariables,
            );
            do {
                $previousRenderedFileName = $renderedFileName;
                $renderedFileName         = str_replace(['//', '\\\\'], DIRECTORY_SEPARATOR, $renderedFileName);
            } while ($previousRenderedFileName !== $renderedFileName);

            $fileContents = self::readTemplateFileContents($fileName);
            if ($fileContents === false) {
                continue;
            }

            yield new File(
                $renderedFileName,
                render(
                    $fileContents,
                    $templateVariables,
                ),
            );
        }
    }

    private static function readTemplateFileContents(string $fileName): string|false
    {
        if (self::$readTemplateFileContentsOverride !== null) {
            return (self::$readTemplateFileContentsOverride)($fileName);
        }

        /** @phpstan-ignore ergebnis.noErrorSuppression */
        return @file_get_contents($fileName);
    }
}
