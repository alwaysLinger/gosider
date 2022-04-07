## swoole and golang ipc demo

## swoole process module exec go excutable file as sider car, use goroutine complete swoole coroutine

### hub.php

```php
<?php

require 'vendor/autoload.php';

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
    dump($resp->getResp());
    // $taskManager['test'] = new Task(6666, 'asddfgdfg');
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

$t1 = new Task(1111, 'aaaa');
$t2 = new Task(2222, 'bbbb');
$t3 = new Task(3333, 'cccc');
$t4 = new Task(4444, 'dddd');

$bus = new Bus(compact('t1', 't2', 't3', 't4'));
$bus->send();


```

### gosider

```golang
package main

import (
	"context"
	"github.com/alwaysLinger/gosider/pkg/hub"
)

func main() {
	h := hub.DefaultHub(func(ctx context.Context, bytes []byte) ([]byte, error) {
		return []byte{'a', 'b', 'c'}, nil
	})

	h.Start()
}
```
```shell
php main.php --bin /path/to/your/goexecfile
php client.php
```