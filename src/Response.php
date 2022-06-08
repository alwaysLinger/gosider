<?php

declare(strict_types=1);

namespace Al\GoSider;

use Al\GoSider\Contracts\Packer;
use Google\Protobuf\Internal\Message;

class Response
{
    protected array|Message $resp;

    public function __construct(
        protected string $raw,
        protected Packer $packer,
    )
    {
        $this->resp = $this->parseResp($this->raw);
    }

    public function getRaw(): string
    {
        return $this->raw;
    }

    private function parseResp(string $raw): array|Message
    {
        return $this->packer->deserializeReply($raw);
    }

    public function getResp(): array|Message
    {
        return $this->resp;
    }
}