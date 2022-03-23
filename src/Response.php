<?php

declare(strict_types=1);

namespace Al\GoSider;

class Response
{
    protected array $resp;

    public function __construct(
        protected string $raw,
    )
    {
        $this->resp = $this->paseResp($this->raw);
    }

    /**
     * @return string
     */
    public function getRaw(): string
    {
        return $this->raw;
    }

    private function paseResp(string $raw): array
    {
        $msg['id'] = unpack('N', substr($raw, 0, 4))[1];
        $msg['len'] = unpack('N', substr($raw, 4, 8))[1];
        $msg['resp'] = substr($raw, 8);
        return $msg;
    }

    /**
     * @return array
     */
    public function getResp(): array
    {
        return $this->resp;
    }
}