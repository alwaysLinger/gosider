<?php

declare(strict_types=1);

namespace Al\GoSider\Concracts;

interface Hubber
{
    public function start(array $settings): void;
}