<?php

namespace FSMgasm;

use Iterator;

abstract class StateHolder extends State implements Iterator
{

    protected int $current = 0;

    /* @var State[] $states */
    protected array $states;

    public function __construct(array $states = [])
    {
        $this->states = $states;
    }

    public function current(): State
    {
        return $this->states[$this->current];
    }

    public function next(): void
    {
        ++$this->current;
    }

    public function key(): int
    {
        return $this->current;
    }

    public function valid(): bool
    {
        return isset($this->states[$this->current]);
    }

    public function rewind(): void
    {
        $this->current = 0;
    }

    public function add(State $state): void
    {
        $this->states[] = $state;
    }

    public function addAll(array $newStates): void
    {
        $this->states[] = $newStates;
    }

    public function frozen(): void
    {
        $this->setFrozen(true);
    }

    public function setFrozen(bool $frozen): void
    {
        foreach ($this->states as $state) {
            $state->setFrozen($frozen);
        }

        parent::setFrozen($frozen);
    }

    public function unfrozen(): void
    {
        $this->setFrozen(false);
    }
}