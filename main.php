<?php

include "vendor/autoload.php";

use Al\GoSider\HubManager;
use Al\GoSider\Hub;
use Al\GoSider\Response;
use Al\GoSider\Task;
use Al\GoSider\TaskManager;

$h = new Hub(
    bin: getopt(',', ['bin:'])['bin'],
    taskManager: new TaskManager(),
);
$h->onSuccess(function (Response $resp, TaskManager $taskManager) {
    /** @var \Messages\Reply $reply */
    $reply = $resp->getResp();
    dump(json_decode($reply->getContext(), true));
    dump($reply->getTaskId());
    dump(json_decode($reply->getResponse(), true));
    // $taskManager[12345] = new Task(6666, [
    //     'task' => [
    //         'a' => '中国',
    //         'b' => 'world',
    //     ],
    //     'context' => [
    //         'c' => '中国',
    //         'd' => 'world',
    //     ],
    // ]);;
    // $taskManager->send();
});
// TODO
$h->onFail(function (Response $resp, TaskManager $taskManager) {

});
// TODO
$h->onTime(function (Response $resp, TaskManager $taskManager) {

});

$hm = new HubManager($h);

$hm->start();