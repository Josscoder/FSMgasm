<?php

namespace FSMgasm;

class StateGroup extends StateHolder {
	
	protected function onStart(): void {
		foreach ($this->states as $state) {
			$state->start();
		}
	}
	
	protected function onUpdate(): void {
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
	
	protected function onEnd(): void {
		foreach ($this->states as $state) {
			$state->end();
		}
	}
	
	public function isReadyToEnd(): bool {
		$isReadyToEnd = true;
		
		foreach ($this->states as $state) {
			if (!$state->isReadyToEnd()) {
				$isReadyToEnd = false;
			}
		}
		
		return $isReadyToEnd;
	}
	
	protected function getDuration(): int {
		return is_null($this->duration) ? 0 : $this->duration;
	}
}