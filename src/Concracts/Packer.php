<?php

declare(strict_types=1);

namespace Al\GoSider\Concracts;

use Google\Protobuf\Internal\Message;

interface Packer
{
    public function serializeTask(array $task): string;

    public function deserializeReply(string $reply): array|Message;

    public function pack(array $task): string;

    public function unpack(string $buffer): int;
}