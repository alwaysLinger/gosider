<?php

declare(strict_types=1);

namespace Al\GoSider;

use Al\GoSider\Concracts\Packer;
use Al\GoSider\Packers\ProtobufPacker;
use Al\GoSider\Packers\StreamPacker;
use Google\Protobuf\Internal\Message;
use Stringable;

class Task implements Stringable
{
    public const STREAM = 1;
    public const PROTOBUF = 2;
    private Packer $packer;

    public function __construct(
        private int      $id,
        private array    $task,
        private string   $request = '',
        private int      $type = self::PROTOBUF,
        private ?Message $message = null,
        private ?Message $reply = null,
    )
    {
        $this->packer = $this->type == self::STREAM ?
            StreamPacker::getInstance() :
            ProtobufPacker::getInstance($this->message, $this->reply);
    }

    public function __toString(): string
    {
        return !empty($this->request) ? $this->request : $this->request = $this->pack();
    }

    private function pack(): string
    {
        return $this->packer->pack(['task_id' => $this->id] + $this->task);
    }
}