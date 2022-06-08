<?php

declare(strict_types=1);

namespace Al\GoSider\Traits;

trait Singleton
{
    private static object $instance;

    static function getInstance(...$args): static
    {
        if (!isset(self::$instance)) {
            self::$instance = new static(...$args);
        }

        return self::$instance;
    }
}
