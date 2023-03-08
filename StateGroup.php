<?php

namespace FSMgasm;

class StateGroup extends StateHolder
{

    public function isReadyToEnd(): bool
    {
        $isReadyToEnd = true;

        foreach ($this->states as $state) {
            if (!$state->isReadyToEnd()) {
                $isReadyToEnd = false;
            }
        }

        return $isReadyToEnd;
    }

    protected function onStart(): void
    {
        foreach ($this->states as $state) {
            $state->start();
        }
    }

    protected function onUpdate(): void
    {
        $allEnded = true;

        foreach ($this->states as $state) {
            $state->update();

            if (!$state->hasEnded()) {
                $allEnded = false;
            }
        }

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

    protected function getDuration(): int
    {
        $statesDuration = array_map(function ($state) {
            return $state->getDuration();
        }, $this->states);
        $maxDuration = max($statesDuration);

        return empty($statesDuration) ? 0 : $maxDuration;
    }
}