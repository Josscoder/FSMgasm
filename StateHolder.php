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

    public function previous(): void
    {
        var_dump($this->key());
        $this->current()->cleanup();
        $this->current = max($this->key() - 1, 0);
        var_dump($this->key());

        if ($this->current() instanceof StateSeries) {
            $this->current()->start();
        }
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
        $this->states = array_merge($this->states, $newStates);
    }

    public function freeze(): void
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

    public function unfreeze(): void
    {
        $this->setFrozen(false);
    }
}