<?php

declare(strict_types=1);

namespace WyriHaximus\Tests\SubSplitTools\TestUtilities;

use Throwable;

final readonly class ThrowingGet implements GetBehavior
{
    public function __construct(public Throwable $throwable)
    {
    }
}
