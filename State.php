<?php

namespace FSMgasm;

use Exception;

abstract class State
{

    protected ?int $duration = null;
    private bool $started = false;
    private bool $ended = false;
    private bool $frozen = false;
    private int $startTime;
    private bool $updating = false;

    public function hasStarted(): bool
    {
        return $this->started;
    }

    public function hasEnded(): bool
    {
        return $this->ended;
    }

    public function isFrozen(): bool
    {
        return $this->frozen;
    }

    public function setFrozen(bool $frozen): void
    {
        $this->frozen = $frozen;
    }

    public function frozen(): void
    {
        $this->setFrozen(true);
    }

    public function unfrozen(): void
    {
        $this->setFrozen(false);
    }

    public function start(): void
    {
        if ($this->started || $this->ended) {
            return;
        }

        $this->started = true;
        $this->startTime = time();

        try {
            $this->onStart();
        } catch (Exception) {
            $className = get_class($this);
            print_r("Exception during $className start\n");
        }
    }

    protected abstract function onStart(): void;

    public function update(): void
    {
        if (!$this->started || $this->ended || $this->updating) {
            return;
        }

        $this->updating = true;

        if ($this->isReadyToEnd() && !$this->frozen) {
            $this->end();
            return;
        }

        try {
            $this->onUpdate();
        } catch (Exception) {
            $className = get_class($this);
            print_r("Exception during $className update\n");
        }

        $this->updating = false;
    }

    public function isReadyToEnd(): bool
    {
        return $this->ended || $this->getRemainingDuration() == 0;
    }

    public function getRemainingDuration(): int
    {
        $sinceStart = $this->startTime - time();
        $remaining = $this->getDuration() - $sinceStart;

        return max($remaining, 0);
    }

    protected abstract function getDuration(): int;

    public function end(): void
    {
        if (!$this->started || $this->ended) {
            return;
        }

        $this->ended = true;

        try {
            $this->onEnd();
        } catch (Exception) {
            $className = get_class($this);
            print_r("Exception during $className end\n");
        }
    }

    protected abstract function onEnd(): void;

    protected abstract function onUpdate(): void;
}