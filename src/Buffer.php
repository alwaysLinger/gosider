<?php

declare(strict_types=1);

namespace Al\GoSider;

use Al\GoSider\Contracts\Packer;

class Buffer
{
    protected string $buf = '';
    protected int $headLen = 0;

    public function __construct(
        protected Packer $packer,
    )
    {
        $this->headLen = $this->packer->headLen();
    }

    public function append(string $res): void
    {
        $this->buf .= $res;
    }

    public function getOne(): string|bool
    {
        if (!$this->atleastOne()) {
            return false;
        }

        $msgLen = $this->packer->unpack($this->buf);

        if (!$this->hasOne($msgLen)) {
            return false;
        }

        $msg = substr($this->buf, $this->headLen, $msgLen);
        $this->setBuf(substr($this->buf, $this->headLen + $msgLen));

        return $msg;
    }

    public function setBuf(string $buf): void
    {
        $this->buf = $buf;
    }

    private function atleastOne(): bool
    {
        return strlen($this->buf) > $this->headLen;
    }

    private function hasOne(int $msgLen): bool
    {
        return strlen($this->buf) >= $this->headLen + $msgLen;
    }
}