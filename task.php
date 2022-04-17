<?php

# some code that may be got blocked in swoole,
# just use Task class it make unblocked
require 'vendor/autoload.php';

use Al\GoSider\Bus;
use Al\GoSider\Task;

$t1 = new Task(1111, [
    'task' => [
        'a' => '中国',
        'b' => 'world',
    ],
    'context' => [
        'c' => '中国',
        'd' => 'world',
    ],
]);
$t2 = new Task(2222, [
    'task' => [
        'a' => '中国',
        'b' => 'world',
    ],
    'context' => [
        'c' => '中国',
        'd' => 'world',
    ],
]);
$t3 = new Task(1234, [
    'task' => [
        'a' => '中国',
        'b' => 'world',
    ],
    'context' => [
        'c' => '中国',
        'd' => 'world',
    ],
]);
$t4 = new Task(5555, [
    'task' => [
        'a' => '中国',
        'b' => 'world',
    ],
    'context' => [
        'c' => '中国',
        'd' => 'world',
    ],
]);

$bus = new Bus(compact('t1', 't2', 't3', 't4'));
$bus->send();