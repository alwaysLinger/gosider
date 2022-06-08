<?php

declare(strict_types=1);

namespace Al\GoSider\Contracts;

use Google\Protobuf\Internal\Message;

interface Packer
{
    public function headLen(): int;

    public function serializeTask(array $task): string;

    public function deserializeReply(string $reply): array|Message;

    public function pack(array $task): string;

    public function unpack(string $buffer): int;
}