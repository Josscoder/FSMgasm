<?php

namespace FSMgasm;

class StateSwitch
{

    protected ?State $currentState = null;

    public function changeState(State $newState): void
    {
        if (!is_null($this->currentState)) {
            $this->currentState->end();
        }
        $this->currentState = $newState;
        $newState->start();
    }

    public function update(): void
    {
        if (!is_null($this->currentState)) {
            $this->currentState->update();
        }
    }
}