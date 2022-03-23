<?php

use Swoole\Coroutine;
use Swoole\Coroutine\Socket;

use function Swoole\Coroutine\run;

// $str = '1234';
// $data = 'hello';
// $p = $str . pack('N', strlen($data)) . $data;
//
// fwrite(STDOUT, $p);
// file_put_contents('a.txt', $p);

run(function () {
    $socket = new Socket(AF_INET, SOCK_STREAM, 0);

    $retval = $socket->connect('127.0.0.1', 9527);
    while ($retval) {
        // $n = $socket->send('hello');
        // var_dump($n);
        //
        // $data = $socket->recv();
        // var_dump($data);

        //发生错误或对端关闭连接，本端也需要关闭
        // if ($data === '' || $data === false) {
        //     echo "errCode: {$socket->errCode}\n";
        //     $socket->close();
        //     break;
        // }
        $str = '1234';
        $data = 'china';
        $p = $str . pack('N', strlen($data)) . $data;

        // for ($i = 0; $i <= 100; $i++) {
            $socket->send($p);
        // }


        Coroutine::sleep(5.0);
        $socket->close();
        break;
    }
    // var_dump($retval, $socket->errCode, $socket->errMsg);
});



