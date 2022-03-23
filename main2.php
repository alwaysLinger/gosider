<?php

require 'vendor/autoload.php';

use Al\GoSider\HubManager;
use Al\GoSider\Hub;
use Al\GoSider\Response;
use Al\GoSider\TaskManager;


$h = new Hub(
    bin: getopt(',', ['bin'])['bin'],
    taskManager: new TaskManager(),
);
$h->onSuccess(function (Response $resp, TaskManager $taskManager) {
    // dump($resp, $taskManager);
});
// TODO
$h->onFail(function (Response $resp, TaskManager $taskManager) {

});
// TODO
$h->onTime(function (Response $resp, TaskManager $taskManager) {

});

$hm = new HubManager($h);

$hm->start();
