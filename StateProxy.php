<?php

namespace FSMgasm;

abstract class StateProxy extends State
{

    private StateSeries $stateSeries;

    public function __construct(StateSeries $series)
    {
        $this->stateSeries = $series;
    }

    protected function onStart(): void
    {
        $this->stateSeries->addNextList($this->createStates());
    }

    /*** @return State[] */
    protected abstract function createStates(): array;

    protected function onUpdate(): void
    {
    }

    protected function onEnd(): void
    {
    }

    protected function getDuration(): int
    {
        return 0;
    }
}