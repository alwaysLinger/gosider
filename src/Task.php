<?php

declare(strict_types=1);

namespace Al\GoSider;

use Stringable;

class Task implements Stringable
{
    public function __construct(
        private int    $id,
        private string $task,
        private string $request = '',
    )
    {

    }

    public function __toString(): string
    {
        return !empty($this->request) ? $this->request : $this->request = $this->pack();
    }

    private function pack(): string
    {
        return pack('NN', $this->id, strlen($this->task)) . $this->task;
    }
}