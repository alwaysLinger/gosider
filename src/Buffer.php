<?php

declare(strict_types=1);

namespace Al\GoSider;

class Buffer
{
    protected string $buf = '';

    public function append(string $res): void
    {
        $this->buf .= $res;
    }

    public function getOne(): string|bool
    {
        if (strlen($this->buf) <= 8) {
            return false;
        }

        $msgLen = unpack('N', substr($this->buf, 4, 8))[1];
        if (strlen($this->buf) < 8 + $msgLen) {
            return false;
        }
        $msg = substr($this->buf, 0, 8 + $msgLen);
        $this->setBuf(substr($this->buf, 8 + $msgLen));
        return $msg;
    }

    public function setBuf(string $buf): void
    {
        $this->buf = $buf;
    }
}