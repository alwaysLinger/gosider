<?php

declare(strict_types=1);

namespace Al\GoSider;

class Response
{
    public function __construct(
        protected string $raw,
    )
    {
    }

    /**
     * @return string
     */
    public function getRaw(): string
    {
        return $this->raw;
    }
}