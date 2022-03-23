<?php

declare(strict_types=1);

namespace Al\GoSider;

use ArrayIterator;

class BusIterator extends ArrayIterator
{
    public function current(): string
    {
        return (string)parent::current();
    }
}