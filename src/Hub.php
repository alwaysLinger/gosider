<?php

declare(strict_types=1);

namespace Al\GoSider;

use Closure;
use Swoole\Coroutine;
use Swoole\Coroutine\Channel;
use Swoole\Coroutine\Server\Connection;
use Swoole\Exception;
use Swoole\Process;
use function Swoole\Coroutine\run;
use function Swoole\Coroutine\go;
use Swoole\Coroutine\Server;
use Swoole\Timer;

class Hub implements Hubber
{
    protected Process $process;
    protected Server $server;
    protected ?Closure $success = null;
    protected ?Closure $fail = null;
    protected ?Closure $time = null;
    protected Buffer $buffer;
    protected bool $running = true;

    public function __construct(
        private string      $bin,
        private TaskManager $taskManager,
        private string      $host = '127.0.0.1',
        private int         $port = 9527,
        private bool        $daemon = false,
        private float       $timeout = 1,
        private string      $siderName = 'gosider',
        private array       $protocols = [
            'open_length_check' => true,
            'package_length_offset' => 4,
            'package_length_type' => 'N',
            'package_body_offset' => 8,
            'open_tcp_nodelay' => true,
        ],
    )
    {
        $this->buffer = new Buffer();
    }

    // private function daemon(): void
    // {
    //     if (!$this->daemon) {
    //         return;
    //     }
    //     Process::daemon();
    // }

    private function execSider(): void
    {
        $this->process = new Process(callback: function (Process $process) {
            $process->name($this->siderName);
            // $process->exec('/usr/local/bin/php', ['/Users/al/code/go/yolo/gosider/index.php']);
            // $process->exec('/usr/local/bin/php', ['/Users/al/code/go/yolo/gosider/sider.php']);
            // $process->exec('/Users/al/code/go/yolo/gosider/main', []);
            $process->exec($this->bin, []);
        }, redirect_stdin_and_stdout: true, pipe_type: SOCK_STREAM);
    }

    /**
     * @throws Exception
     */
    public function start(): void
    {
        if (is_null($this->success)) {
            throw new Exception('recv callback not set');
        }
        if (is_null($this->fail)) {
            throw new Exception('fail callback not set');
        }
        if (is_null($this->time)) {
            throw new Exception('fail callback not set');
        }
        // $this->daemon();
        $this->execSider();
        $this->process->start();
        $this->dispatch();
    }

    private function dispatch(): void
    {
        run(function () {
            $this->wait();
            // $this->process->exportSocket()->setProtocol($this->protocols);
            $chan = new Channel(0);
            go(fn() => $this->internalHub($chan));
            go(fn() => $this->recv());
            go(fn() => $this->taskManagerConnect($chan));
        });
    }

    private function wait(): void
    {
        Process::signal(SIGCHLD, function ($sig) {
            while ($ret = Process::wait(false)) {
                // TODO
                dump(sprintf('pid:%s wait success', $ret['pid']));
            }
            $this->server->shutdown();
            $this->running = false;
        });
    }

    /**
     * @throws Exception
     */
    private function internalHub(Channel $chan): void
    {
        // dump('internalserver');
        $this->server = new Server(host: $this->host, port: $this->port);
        $this->server->set($this->protocols);
        $this->server->handle(function (Connection $conn) {
            // dump($conn->exportSocket()->getpeername());
            // dump($conn->exportSocket()->getsockname());
            while ($this->running) {
                $tasks = $conn->recv($this->timeout);
                // dump('tasks:' . $tasks);
                if ($tasks === false || $tasks === '') {
                    $conn->close();
                    break;
                }
                // dump(strlen($tasks));
                // dump(unpack('N', substr($tasks, 0, 4))[1], substr($tasks, 8));
                $this->writeTasks($tasks);
            }
        });
        $chan->push(Coroutine::getCid());
        $this->server->start();
    }

    private function taskManagerConnect(Channel $chan)
    {
        $scid = $chan->pop();
        Timer::after(10, function () use ($scid) {
            if (Coroutine::exists($scid)) {
                $this->taskManager->setBus(new Bus());
            }
        });
    }

    private function recv(): void
    {
        dump('start recv...');
        while ($this->running) {
            $resp = $this->process->exportSocket()->recv((int)$this->timeout);
            if ($resp === false) {
                continue;
            }
            $this->buffer->append($resp);
            while ($msg = $this->buffer->getOne()) {
                dump('recv:' . $msg, strlen($msg));
                $this->handleResp($resp);
            }
        }
    }

    public function onSuccess(Closure $cb): void
    {
        $this->success = $cb;
    }

    public function onFail(Closure $cb): void
    {
        $this->fail = $cb;
    }

    public function onTime(Closure $cb): void
    {
        $this->time = $cb;
    }

    private function writeTasks(string $tasks): void
    {
        $this->process->exportSocket()->send($tasks);
    }

    private function handleResp(string $resp): void
    {
        // TODO
        call_user_func($this->success, new Response($resp), $this->taskManager);
    }

}