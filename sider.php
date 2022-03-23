<?php

use function Swoole\Coroutine\run;
use function Swoole\Coroutine\go;

run(function () {
    go(function () {
        while (1) {
            // stream_get_contents();
            $res = stream_get_line(STDIN, 12);
            // $res = fread(STDIN, 1024);
            if (strlen($res) === 0) {
                continue;
            }
            file_put_contents('abc.txt', strlen($res) . "\n", FILE_APPEND);
            $id = 12345;
            $data = 'abasdasd';
            // $payload = pack('NN', $id, strlen($data)) . $data;
            $payload = pack('NN', 1234, 1) . $data;
            // fwrite(STDOUT, $payload);
            stream_socket_sendto(STDOUT, $payload);
            fflush(STDOUT);
        }
    });
    // go(function () {
    // });
});