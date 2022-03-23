<?php

require 'vendor/autoload.php';

use Al\GoSider\Bus;
use Al\GoSider\Task;

$t1 = new Task(1111, 'aaaa');
$t2 = new Task(2222, 'bbbb');
$t3 = new Task(3333, 'cccc');
$t4 = new Task(4444, 'dddd');

$bus = new Bus(compact('t1', 't2', 't3', 't4'));
$bus->send();

