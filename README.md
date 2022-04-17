## swoole and golang ipc demo

## swoole process module exec go excutable file as sider car, use goroutine complete swoole coroutine

### hub.php

```php
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
    //         'a' => 'swoole',
    //         'b' => 'world',
    //     ],
    //     'context' => [
    //         'c' => 'golang',
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

```

### 

```php
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

$bus = new Bus(compact('t1', 't2'));
$bus->send();

```

### gosider

```golang
package main

import (
	"context"
	"encoding/json"
	"github.com/alwaysLinger/gosider/pkg/hub"
	"github.com/alwaysLinger/gosider/pkg/pb"
)

func main() {
	h := hub.NewHub(func(ctx context.Context, req interface{}) (interface{}, error) {
		task, _ := req.(*pb.Task)

		return &pb.Reply{
			TaskId:   task.GetTaskId(),
			Status:   123,
			Response: jsonResp(),
			Context:  task.GetContext(),
		}, nil
	})

	h.Start()
}

type resp struct {
	FieldA string `json:"field_a"`
	FieldB string `json:"field_b"`
	FieldC string `json:"field_c"`
}

func jsonResp() []byte {

	marshal, err := json.Marshal(resp{
		FieldA: "hello",
		FieldB: "swoole",
		FieldC: "golang",
	})
	if err != nil {
		return nil
	}
	return marshal
}

```
```shell
php main.php --bin /path/to/your/goexecfile
php client.php
```