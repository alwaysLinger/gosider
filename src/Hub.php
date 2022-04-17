<?php

declare(strict_types=1);

namespace Al\GoSider;

use Al\GoSider\Concracts\Hubber;
use Al\GoSider\Concracts\Packer;
use Al\GoSider\Packers\ProtobufPacker;
use Closure;
use Exception;
use Swoole\Coroutine;
use Swoole\Coroutine\Channel;
use Swoole\Coroutine\Server;
use Swoole\Coroutine\Server\Connection;
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
    protected array $settings;
    protected Packer $packer;

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
    public function start(array $settings): void
    {
        if (is_null($this->success)) {
            $this->throwException('recv callback not set');
        }
        if (is_null($this->fail)) {
            $this->throwException('fail callback not set');
        }
        if (is_null($this->time)) {
            $this->throwException('fail callback not set');
        }

        $settings['packer'] ??= ProtobufPacker::class;

        $this->settings = $settings;
        $this->packer = $settings['packer']::getInstance();
        $this->buffer = new Buffer(packer: $this->packer);
        $this->execSider();
        $this->process->start();
        $this->dispatch();
    }

    private function dispatch(): void
    {
        Coroutine::set(['hook_flags' => SWOOLE_HOOK_ALL]);
        run(function () {
            $stopChan = new Channel();
            $this->wait($stopChan);
            // got no idea why this socket protocol setting does not work, so implement one
            // $this->process->exportSocket()->setProtocol($this->protocols);
            go(fn() => $this->internalHub());
            go(fn() => $this->recv());
            go(fn() => $this->ctrl($stopChan));
        });
    }

    private function wait(Channel $stopChan): void
    {
        Process::signal(SIGCHLD, function ($sig) use ($stopChan) {
            $ret = Process::wait(false);
            // TODO
            // dump(sprintf('pid:%s wait success', $ret['pid']));
            $stopChan->push(1);
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
            while (1) {
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
        while (1) {
            $resp = $this->process->exportSocket()->recv((int)$this->timeout);
            if ($resp === false) {
                break;
            }
            $this->buffer->append($resp);
            while ($msg = $this->buffer->getOne()) {
                $this->handleResp($msg);
            }
        }
    }

    private function ctrl(Channel $stopChan)
    {
        $stopChan->pop();
        $this->server->shutdown();
        $this->process->exportSocket()->close();
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
        call_user_func($this->success, new Response(raw: $resp, packer: $this->packer), $this->taskManager);
    }

    /**
     * @throws Exception
     */
    private function throwException(string $message)
    {
        throw new Exception($message);
    }
}