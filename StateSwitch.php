<?php

namespace FSMgasm;

class StateSwitch {
	
	protected ?State $currentState = null;
	
	public function changeState(State $newState): void {
		$this->currentState->end();
		$this->currentState = $newState;
		$newState->start();
	}
	
	public function update(): void {
		$this->currentState->update();
	}
}