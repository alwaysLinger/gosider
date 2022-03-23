<?php

declare(strict_types=1);

namespace Al\GoSider;

use ArrayObject;
use Exception;

class Bus extends ArrayObject
{
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
            throw new Exception('bus connect internal server fail');
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
        $res = stream_socket_sendto($this->client, $tasks);
        // $data = pack('NN', 123, strlen('hello')) . 'hello';
        // $res = stream_socket_sendto($this->client, $data . $data);

        // dump($res);
        dump($tasks, strlen($tasks));
        $this->exchangeArray([]);
    }
}