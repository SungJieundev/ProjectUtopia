<?php

declare(strict_types=1);

namespace alvin0319\EconomyAPI\session;

use alvin0319\EconomyAPI\EconomyAPI;
use alvin0319\SessionManager\session\BaseSession;
use pocketmine\player\Player;
use SOFe\AwaitGenerator\Await;
use function count;

final class EconomySession extends BaseSession{

	/** @var int[] */
	private array $currencies = [];

	public function __construct(string $name, ?Player $player = null, array $currencies = []){
		parent::__construct($name, $player);
		if(count($currencies) > 0){
			$this->loaded = true;
		}else{
			Await::f2c(function() : \Generator{
				for($i = 0; $i < 3; $i++){
					$rows = yield from EconomyAPI::$database->economyapiGet($this->name, );
				}
			});
		}
	}

	public function save() : void{
	}
}