<?php

declare(strict_types=1);

namespace Al\GoSider;

use ArrayObject;
use Exception;

class TaskManager extends ArrayObject
{
    use ValidTask;

    protected ?Bus $bus = null;
    protected ArrayObject $failed;
    protected ArrayObject $overtimed;

    public function __construct(
        protected string $name = 'default',
    )
    {
        $this->failed = new ArrayObject();
        $this->overtimed = clone $this->failed;

        parent::__construct();
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    public function retryFailed(): self
    {
        return $this->exchangeTasks($this->failed);
    }

    public function retryOvertimed(): self
    {
        return $this->exchangeTasks($this->overtimed);
    }

    private function exchangeTasks(ArrayObject $tasks): self
    {
        $this->getBus()->exchangeArray($tasks);
        return $this;
    }

    /**
     * @throws Exception
     */
    public function send(): void
    {
        if (count($this->getBus()) === 0) {
            $this->exchangeTasks($this);
        }
        $this->getBus()->send();
    }

    /**
     * @return ArrayObject
     */
    public function getFailed(): ArrayObject
    {
        return $this->failed;
    }

    /**
     * @return ArrayObject
     */
    public function getOvertimed(): ArrayObject
    {
        return $this->overtimed;
    }

    /**
     * @return Bus
     */
    private function getBus(): Bus
    {
        return $this->bus ??= new Bus();
    }

    /**
     * @param Bus $bus
     */
    public function setBus(Bus $bus): void
    {
        $this->bus = $bus;
    }
}