<?php

declare(strict_types=1);

namespace Al\GoSider;

use Al\GoSider\Traits\ValidTask;
use ArrayObject;
use Exception;

class Bus extends ArrayObject
{
    use ValidTask;

    /**
     * @var false|resource
     */
    private $client;

    /**
     * @throws Exception
     */
    public function __construct(
        protected array $tasks = [],
    )
    {
        $this->client = stream_socket_client('tcp://127.0.0.1:9527', $errno, $errstr, 1.5);
        if (!$this->client) {
            throw new Exception(sprintf('bus connect internal server fail, errno: %d, errstr: %s', $errno, $errstr));
        }

        parent::__construct(array: $this->tasks, iteratorClass: BusIterator::class);
    }

    /**
     * @throws Exception
     */
    public function send(): void
    {
        if (count($this) === 0) {
            throw new Exception('got no tasks');
        }

        $tasks = implode('', (array)$this);
        stream_socket_sendto($this->client, $tasks);

        $this->exchangeArray([]);
    }

    public function __destruct()
    {
        if (is_resource($this->client)) {
            @fclose($this->client);
        }
    }
}