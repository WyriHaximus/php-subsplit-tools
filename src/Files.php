<?php

namespace WyriHaximus\SubSplitTools;

use function WyriHaximus\Twig\render;

final class Files
{
    public static function setUp(string $templates, string $destination, array $templateVariables): void
    {
        foreach ([
            ...glob(rtrim($templates, '/') . '/**', GLOB_NOSORT),
            ...glob(rtrim($templates, '/') . '/**/*', GLOB_NOSORT),
        ] as $fileName) {
            echo $fileName, PHP_EOL;

            if (!is_file($fileName)) {
                continue;
            }
            $newFilename = render(
                $destination . DIRECTORY_SEPARATOR . substr($fileName, strlen($templates)),
                $templateVariables,
            );

            @mkdir(dirname($newFilename), 0744, true);
            file_put_contents(
                $newFilename,
                render(
                    file_get_contents($fileName),
                    $templateVariables,
                ),
            );
        }
    }
}
