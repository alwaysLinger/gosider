<?php

declare(strict_types=1);

namespace Al\GoSider;

use Al\GoSider\Traits\ValidTask;
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

    public function loadFailedTasks(): null|self
    {
        return $this->exchangeFailed();
    }

    /**
     * @throws Exception
     */
    public function retryFailed()
    {
        $this->exchangeFailed()?->send();
    }

    public function loadOvertimedTasks(): null|self
    {
        return $this->exchangeOvertimed();
    }

    /**
     * @throws Exception
     */
    public function retryOvertimed()
    {
        $this->exchangeOvertimed()?->send();
    }

    private function exchangeFailed(): null|self
    {
        if ($this->failed->count() === 0) {
            return null;
        }

        $this->exchangeTasks($this->failed);
        $this->failed->exchangeArray([]);

        return $this;
    }

    private function exchangeOvertimed(): null|self
    {
        if ($this->overtimed->count() === 0) {
            return null;
        }

        $this->exchangeTasks($this->overtimed);
        $this->overtimed->exchangeArray([]);

        return $this;
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
        if ($this->getBus()->count() === 0) {
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