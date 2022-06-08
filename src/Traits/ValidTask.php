<?php

declare(strict_types=1);

namespace Al\GoSider\Traits;

use ArrayObject;
use InvalidArgumentException;
use RuntimeException;
use Stringable;

trait ValidTask
{
    /**
     * @throws InvalidArgumentException
     */
    public function offsetSet($key, $value)
    {
        if (!is_subclass_of($this, ArrayObject::class)) {
            throw new RuntimeException('wrong context with this trait');
        }

        if (!is_int($key)) {
            throw new InvalidArgumentException("task_id must be an integer");
        }

        if (!$value instanceof Stringable) {
            throw new InvalidArgumentException("a task must be an instance of Stringable");
        }

        parent::offsetSet($key, $value);
    }
}