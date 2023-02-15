<?php

declare(strict_types=1);

namespace alvin0319\SessionManager\session;

use alvin0319\SessionManager\Loader;
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

	abstract public function save(bool $offline = true) : void;

	abstract public function onPlayerQuit() : void;

	final public function forceRemoveSession() : void{
		$this->save();
		$this->onPlayerQuit();
		Loader::getInstance()->removeSession($this);
	}

	public function isLoaded() : bool{
		return $this->loaded;
	}

	public function tick() : void{
	}
}
