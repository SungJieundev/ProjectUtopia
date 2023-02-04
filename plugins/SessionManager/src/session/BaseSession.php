<?php

declare(strict_types=1);

namespace alvin0319\SessionManager\session;

use pocketmine\player\Player;

abstract class BaseSession{

	protected bool $loaded = false;

	public function __construct(protected string $name, protected ?Player $player = null){ }

	public function getName() : string{
		return $this->name;
	}

	public function getPlayer() : ?Player{
		return $this->player;
	}

	abstract public function save() : void;

	public function onPlayerQuit() : void{
	}

	public function isLoaded() : bool{
		return $this->loaded;
	}

	public function tick() : void{
	}
}