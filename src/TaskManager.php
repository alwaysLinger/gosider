<?php

declare(strict_types=1);

namespace Al\GoSider;

use ArrayObject;
use Exception;

class TaskManager extends ArrayObject
{
    protected ?Bus $bus = null;
    protected ArrayObject $failed;
    protected ArrayObject $overtimed;

    public function __construct(
        protected string $name = 'default',
    )
    {
        $this->failed = new ArrayObject();
        $this->overtimed = new ArrayObject();

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
        $this->bus->exchangeArray($tasks);
        return $this;
    }

    /**
     * @throws Exception
     */
    public function send(): void
    {
        if (count($this->bus) === 0) {
            $this->exchangeTasks($this);
        }
        $this->bus->send();
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
    public function getBus(): Bus
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