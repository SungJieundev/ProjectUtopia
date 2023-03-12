<?php

declare(strict_types=1);

namespace alvin0319\SessionManager\session;

use alvin0319\SessionManager\Loader;
use pocketmine\player\Player;

abstract class BaseSession{

	protected bool $loaded = false;

	private int $offlineTick = 0;

	public function __construct(protected string $name, protected ?Player $player = null){ }

	public function getName() : string{
		return $this->name;
	}

	public function getPlayer() : ?Player{
		return $this->player;
	}

	public function isOffline() : bool{
		return $this->player === null || !$this->player->isConnected();
	}

	public function setOnline(Player $player) : void{
		$this->player = $player;
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

	public function offlineTick() : void{
		if($this->player === null){
			if(++$this->offlineTick >= 60){
				$this->forceRemoveSession();
			}
		}
	}
}
