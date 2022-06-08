<?php

declare(strict_types=1);

namespace Al\GoSider\Packers;

use Al\GoSider\Contracts\Packer;
use Al\GoSider\Traits\Singleton;
use Google\Protobuf\Internal\Message;

class StreamPacker implements Packer
{
    use Singleton;

    public const HEADLEN = 8;

    public function pack($task): string
    {
        $task = $this->serializeTask($task);

        return pack('NN', $task['task_id'], strlen($task)) . $task;
    }

    public function unpack(string $buffer): int
    {
        return unpack('N', substr($buffer, 4, 4))[1];
    }

    public function serializeTask(array $task): string
    {
        return json_encode($task, JSON_UNESCAPED_UNICODE);
    }

    public function deserializeReply(string $raw): array|Message
    {
        [$msg['task_id'], $msg['len'], $msg['resp']] = [
            unpack('N', substr($raw, 0, 4))[1],
            unpack('N', substr($raw, 4, 8))[1],
            substr($raw, 8)
        ];

        return $msg;
    }

    public function headLen(): int
    {
        return self::HEADLEN;
    }
}