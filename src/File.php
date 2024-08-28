<?php

declare(strict_types=1);

namespace WyriHaximus\SubSplitTools;

final readonly class File
{
    public function __construct(
        public string $fileName,
        public string $contents,
    ) {
    }
}
