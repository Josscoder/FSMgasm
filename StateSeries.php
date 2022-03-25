<?php

namespace FSMgasm;

use JetBrains\PhpStorm\Pure;

class StateSeries extends StateHolder {

	protected bool $skipping = false;

	#[Pure] public function __construct(array $states) {
		parent::__construct($states);
	}

	public function addNext(State $state): void {
		$this->states[$this->key() + 1] = $state;
	}

	public function addNextList(array $newStates): void {
		$index = 1;

		foreach($newStates as $state) {
			$this->states[$this->key() + $index] = $state;
			++$index;
		}
	}

	public function skip(): void {
		$this->skipping = true;
	}

	protected function onStart(): void {
		if (empty($this->states)) {
			$this->end();

			return;
		}

		$this->current()->start();
	}

	protected function onUpdate(): void {
		$this->current()->update();

		if (($this->current()->isReadyToEnd() &&
				!$this->current()->isFrozen()) || $this->skipping
		) {
			if ($this->skipping) {
				$this->skipping = false;
				$this->current()->end();
				$this->next();
			}

			if ($this->key() >= count($this->states)) {
				$this->end();

				return;
			}

			$this->current()->start();
		}
	}

	public function isReadyToEnd(): bool {
		return ($this->key() == count($this->states) - 1 && $this->current()->isReadyToEnd());
	}

	protected function onEnd(): void {
		if ($this->key() < count($this->states)) {
			$this->current()->end();
		}
	}

	protected function getDuration(): int{
		$duration = null;

		foreach($this->states as $state) {
			$duration = ($duration + $state->getDuration());
		}

		return is_null($duration) ? 0 : $duration;
	}
}