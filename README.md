# ![FSMgasm logo](http://i.imgur.com/hA3h42o.png) FSMgasm

FSMgasm is a PHP [state machine](http://www.skorks.com/2011/09/why-developers-never-use-state-machines/) library.
It is useful to model complex systems, simplify code and facilitate code reuse.
The library is available under [MIT License](https://tldrlegal.com/license/mit-license).

This was originally created by [Minikloon](https://github.com/Minikloon), all credits to him

# Usage

Using FSMgasm is about creating states and composing them together.
A state is simple: it's something with a start, a duration and an end.
Sometimes a state will also do stuff in-between.

### Creating states

To create a state, override State:

```php
<?php

class PrintState extends State {

	private string $toPrint;

	public function __construct(string $toPrint) {
		$this->toPrint = $toPrint;
	}

	protected function onStart() : void {
		print_r($this->toPrint . "\n");
	}

	protected function onUpdate() : void {
		// TODO: Implement onUpdate() method.
	}

	protected function onEnd() : void {
		print_r($this->toPrint . "\n");
	}

	protected function getDuration() : int {
		return 1; //this is in seconds
	}
}
```

Keep in mind **FSMgasm doesn't handle state execution for you**.
This means there is no black magic behind using your newly-created state.

Using your state:

```php
	public function main(): void {
		$state = new PrintState("Hello world!");
		$state->start();
		$state->end();
	}
```

State does guarantee that `onStart()` and `onEnd()` will only be called once and that only a single onUpdate will be
executed at a time.
It also checks that `start()` has been called before continuing execution of `update()` and `end()`.
These guarantees are retained in a multithreaded environment.

### Composing states

There are two classes to help you compose states together.

#### StateSeries

StateSeries lets you compose your states sequentially. It is typical to use a state series as the "main state" of a
system.

```php
	public function main(): void {
		$series = new StateSeries([
			new PrintState("State 1"),
			new PrintState("State 2")
		]);
		$series->start();
		
		while (true) {
			$series->update();
		}
	}
```

StateSeries will take care of checking whether the current state is over and switch to the next state in its update
method.
Typically a state is over when it lasted for more than its duration. Duration is included in State because of how common
it is.
If your state doesn't need duration, you can override `State::isReadyToEnd` to setup your own ending condition.

You can setup a StateSeries either using the vararg constructor, a list of states, or adding them manually after
construction using `StateSeries::add`.
`add` will add a state to the end of the series and can be used after initialization. `addNext` can be used to add a
state right after the current state.

What makes state composition with FSMgasm is that **StateSeries extends State**. This means you can do something like:

```php
	public function main(): void {
		$series = new StateSeries([
			new StateSeries([
				new PrintState("Sub-Series 1, State 1"),
				new PrintState("Sub-Series 1, State 2")
			]),
			new StateSeries([
				new PrintState("Sub-Series 2, State 1"),
				new PrintState("Sub-Series 2, State 2"),
				new PrintState("Sub-Series 2, State 3")
			])
		]);
		$series->start();

		while (true) {
			$series->update();
			sleep(10);
			if ($series->hasEnded()) {
				break;
			}
		}
	}
```

Another features of State (and thus StateSeries) are the `frozen` and `unfrozen` methods.

```php
$this->series->frozen();
$this->series->unfrozen();
```

This prevents State from ending and in the case of StateSeries, stops it from moving to the next state.
Freezing a state series can be useful when testing and debugging.

#### StateGroup

StateGroup lets you compose your states concurrently. *This doesn't mean they'll be executed on different threads*.
All the states within a StateGroup will be started on `StateGroup::start`, similarly with `end`.

```php
	public function main(): void {
		$group = new StateGroup([
			new PrintState("State 1"),
			new PrintState("State 2")
		]);
		$group->start();
		$group->end();
	}
```

StateGroup also extends State.

#### StateProxy

In some cases, you can't know all the states which are going to be needed at initialization ahead of time in a
StateSeries.

For example, when modeling [Build Battle](https://www.youtube.com/watch?v=PXM5Xgjkhwo), the game starts with 12 players
all building at the same time for 5 minutes. After the build time, players are teleported to each build for 30 seconds
one
at a time for judging. Builds of players who left aren't available for judging. This situation can modeled like so:

~~~~
StateSeries:
    1. StateGroup(12 x BuildState)
    2. PlayerCheckStateProxy => Creates 1 VoteState for each player still in the game
    3. AnnounceWinnerState
~~~~

A StateProxy may be implemented like this:

```php
<?php

class TwelveYearsAState extends StateProxy {
	
	public function __construct(StateSeries $series){
		parent::__construct($series);
	}

	protected function createStates() : array {
		$states = [];
		
		for ($i = 1; $i <= 12; $i++) {
			$states[] = new PrintState("Proxied State " . $i);
		}
		
		return $states;
	}
}
```

#### StateSwitch

Not all situations can be easily modeled using a StateSeries, for example a game's menus. The player's navigation
through the menus
could go as such:

~~~~
MainMenuState => OptionMenuState => MainMenuState => StartGameState.
~~~~

This is where StateSwitch comes into play. It's a simple class which can be used as such:

```php
    public function main(): void {
       $switch = new StateSwitch();
       $switch->changeState(new PrintState("First!"));
       $switch->changeState(new PrintState("Second!"));
    }
```

`StateSwitch::update` is provided as a convenience method to update the underlying state.
