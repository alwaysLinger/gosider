<?php

declare(strict_types=1);

namespace Al\GoSider;

use Swoole\Process\Pool;

class HubManager
{
    protected Pool $pool;

    public function __construct(
        private Hubber $hub,
    )
    {
        $this->pool = new Pool(1);
    }

    public function start()
    {
        $this->pool->on('WorkerStart', fn() => $this->hub->start());
        $this->pool->start();
    }
}