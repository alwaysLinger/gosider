<?php

include "vendor/autoload.php";

use Al\GoSider\Hub;
use Al\GoSider\Response;
use Al\GoSider\TaskManager;

$h = new Hub(taskManager: new TaskManager());
$h->onSuccess(function (Response $resp, TaskManager $taskManager) {
    // dump($resp, $taskManager);
});
$h->onFail(function (Response $resp, TaskManager $taskManager) {

});
$h->onTime(function (Response $resp, TaskManager $taskManager) {

});
$h->start();
