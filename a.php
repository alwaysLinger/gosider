<?php

include "vendor/autoload.php";

use Al\GoSider\Task;
use Al\GoSider\Bus;

// $task = new Task(1234, 'abvd');
// dump((string)$task);
// dump(strlen((string)$task));

// $b = new Bus();
// dump(count($b));
$t1 = new Task(121233, 'asdv');
$t2 = new Task(123, 'asdv');
$t3 = new Task(123, 'asdv');
$t4 = new Task(123, 'asdv');

$b = new Bus(compact('t1', 't2', 't3', 't4'));
// $b->send();
dump($b['t1']);
$b->exchangeArray([]);
dump(count($b));


// dump($b);
// dump($b->send());