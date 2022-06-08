<?php

declare(strict_types=1);

namespace Al\GoSider\Packers;

use Al\GoSider\Contracts\Packer;
use Al\GoSider\Traits\Singleton;
use Closure;
use Exception;
use Google\Protobuf\Internal\Message;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Messages\Reply;
use Messages\Task;

class ProtobufPacker implements Packer
{
    use Singleton;

    public const HEADLEN = 8;

    private ?Closure $assembler = null;
    private ?Closure $disassembler = null;

    public function __construct(
        protected ?Message $task = null,
        protected ?Message $reply = null,
    )
    {
    }

    public function pack(array $task): string
    {
        if (!Arr::isAssoc($task)) {
            throw new InvalidArgumentException('a task payload must be an associative array');
        }
        $message = $this->serializeTask($task);

        return pack('NN', $task['task_id'], strlen($message)) . $message;
    }

    public function unpack(string $buffer): int
    {
        return unpack('N', substr($buffer, 4, 4))[1];
    }

    public function setTask(Message $task): void
    {
        $this->task = $task;
    }

    public function setassembler(Closure $assembler): void
    {
        $this->assembler = $assembler;
    }

    public function serializeTask(array $task): string
    {
        return $this->assembleTask($task)->serializeToString();
    }

    protected function assembleTask(array $task): Message
    {
        if (is_null($this->assembler)) {
            $task['task'] ??= [];
            $task['context'] ??= [];
            foreach ($task as $field => $value) {
                if (($field === 'task' || $field === 'context') && !is_array($value)) {
                    throw new InvalidArgumentException('task and context field must be an array');
                }
                $value = is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : $value;
                $this->getTask()->{Str::camel("set_{$field}")}($value);
            }

            return $this->getTask();
        }

        return call_user_func_array($this->assembler, $task);
    }

    public function getTask(): Message
    {
        return $this->task = $this->task ?? new Task();
    }

    /**
     * @throws Exception
     */
    public function deserializeReply(string $reply): array|Message
    {
        if (is_null($this->disassembler)) {
            $this->getReply()->mergeFromString($reply);
            return $this->getReply();
        }

        return call_user_func($this->disassembler, $this->reply);
    }

    public function setDisassemble(Closure $disassembler): void
    {
        $this->disassembler = $disassembler;
    }

    public function getReply(): Message
    {
        return $this->reply = $this->reply ?? new Reply();
    }

    public function headLen(): int
    {
        return self::HEADLEN;
    }
}