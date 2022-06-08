<?php

declare(strict_types=1);

namespace Al\GoSider;

use Al\GoSider\Contracts\Hubber;
use Swoole\Process\Pool;

class HubManager
{
    protected Pool $pool;

    public function __construct(
        private Hubber $hub,
        private array  $settings = [],
    )
    {
        $this->pool = new Pool(1);
    }

    public function start()
    {
        $this->pool->on('WorkerStart', fn() => $this->hub->start($this->settings));
        $this->pool->start();
    }
}