<?php

declare(strict_types=1);

namespace Al\GoSider;

use Closure;
use Swoole\Coroutine\Server;
use Swoole\Coroutine\Server\Connection;
use Swoole\Exception;
use Swoole\Process;
use function Swoole\Coroutine\go;
use function Swoole\Coroutine\run;

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

    private function execSider(): void
    {
        $this->process = new Process(callback: function (Process $process) {
            $process->name($this->siderName);
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
        $this->execSider();
        $this->process->start();
        $this->dispatch();
    }

    private function dispatch(): void
    {
        run(function () {
            $this->wait();
            // got no idea why this socket protocol setting does not work, so implement one
            // $this->process->exportSocket()->setProtocol($this->protocols);
            go(fn() => $this->internalHub());
            go(fn() => $this->recv());
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
    private function internalHub(): void
    {
        $this->server = new Server(host: $this->host, port: $this->port);
        $this->server->set($this->protocols);
        $this->server->handle(function (Connection $conn) {
            while ($this->running) {
                $tasks = $conn->recv($this->timeout);
                if ($tasks === false || $tasks === '') {
                    $conn->close();
                    break;
                }
                $this->writeTasks($tasks);
            }
        });
        $this->server->start();
    }

    private function recv(): void
    {
        while ($this->running) {
            $resp = $this->process->exportSocket()->recv((int)$this->timeout);
            if ($resp === false) {
                continue;
            }
            $this->buffer->append($resp);
            while ($msg = $this->buffer->getOne()) {
                $this->handleResp($msg);
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