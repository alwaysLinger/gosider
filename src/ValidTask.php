<?php

declare(strict_types=1);

namespace Al\GoSider;

use InvalidArgumentException;
use Stringable;

trait ValidTask
{
    /**
     * @throws InvalidArgumentException
     */
    public function offsetSet($key, $value)
    {
        if (!is_string($key)) {
            throw new InvalidArgumentException("task_id must be a string value");
        }

        if (!$value instanceof Stringable) {
            throw new InvalidArgumentException("task must implement __toString method");
        }

        parent::offsetSet($key, $value);
    }
}