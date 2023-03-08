<?php

namespace FSMgasm;

class StateGroup extends StateHolder
{

    protected function onStart(): void
    {
        foreach ($this->states as $state) {
            $state->start();
        }
    }

    protected function onUpdate(): void
    {
        foreach ($this->states as $state) {
            $state->update();
        }

        $allEnded = array_reduce($this->states, function($carry, $state) {
            return $carry && $state->hasEnded();
        }, true); //TODO: need test

        if ($allEnded) {
            $this->end();
        }
    }

    protected function onEnd(): void
    {
        foreach ($this->states as $state) {
            $state->end();
        }
    }

    public function isReadyToEnd(): bool
    {
        return array_reduce($this->states, function($carry, $state) {
            return $carry && $state->isReadyToEnd();
        }, true); //TODO: need test
    }

    protected function getDuration(): int
    {
        $statesDuration = array_map(function ($state) {
            return $state->getDuration();
        }, $this->states);
        $maxDuration = max($statesDuration);

        return empty($statesDuration) ? 0 : $maxDuration;
    }
}