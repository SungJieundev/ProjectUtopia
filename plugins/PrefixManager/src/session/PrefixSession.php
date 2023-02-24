<?php

declare(strict_types=1);

namespace alvin0319\PrefixManager\session;

use alvin0319\PrefixManager\Loader;
use alvin0319\PrefixManager\prefix\Prefix;
use alvin0319\SessionManager\session\BaseSession;
use pocketmine\player\Player;
use pocketmine\utils\Utils;
use SOFe\AwaitGenerator\Await;
use function array_map;
use function array_search;
use function array_values;
use function count;
use function json_encode;

final class PrefixSession extends BaseSession{

	private string $customName = "";

	/** @var Prefix[] */
	private array $prefixes = [];

	private int $selectedPrefix = 0;

	/** @param Prefix[] $prefixes */
	public function __construct(
		string $name,
		?Player $player = null,
		string $customName = "",
		array $prefixes = [],
		int $selectedPrefix = 0
	){
		parent::__construct($name, $player);
		if(count($prefixes) > 0){
			$this->customName = $customName;
			$this->prefixes = $prefixes;
			$this->selectedPrefix = $selectedPrefix;
		}else{
			Await::f2c(function() : \Generator{
				for($i = 0; $i < 3; $i++){
					$rows = yield from Loader::$database->getSession($this->name);
					if(count($rows) > 0){
						if($rows[0]["syncBlocked"] === true && $i < 2){
							yield from Loader::$std->sleep(20);
							continue;
						}
						$this->customName = $rows[0]["customName"];
						$this->selectedPrefix = $rows[0]["selectedPrefix"];
						foreach($rows[0]["prefixes"] as $prefixId){
							$prefix = Loader::getInstance()->prefixManager->getPrefix($prefixId);
							if($prefix !== null){
								$this->prefixes[] = $prefix;
							}
						}
						if(count($this->prefixes) === 0){
							$this->prefixes[] = Loader::getInstance()->prefixManager->getDefaultPrefix();
						}
					}else{
						$this->prefixes[] = Loader::getInstance()->prefixManager->getDefaultPrefix();
						yield from Loader::$database->createSession(
							$this->name,
							"",
							Utils::assumeNotFalse(json_encode(array_map(static fn(Prefix $prefix) => $prefix->id, $this->prefixes))),
							1
						);
					}
					$this->loaded = true;
					break;
				}
			});
		}
	}

	public function save(bool $offline = true) : void{
		Await::g2c(Loader::$database->updateSession(
			$this->name,
			$this->customName,
			Utils::assumeNotFalse(json_encode(array_map(static fn(Prefix $prefix) => $prefix->id, $this->prefixes))),
			$this->selectedPrefix
		));
	}

	public function onPlayerQuit() : void{
		Loader::getInstance()->removeSession($this->name);
	}

	public function getCustomName() : string{
		return $this->customName;
	}

	public function getSelectedPrefix() : Prefix{
		return $this->prefixes[$this->selectedPrefix];
	}

	public function selectPrefix(int $index) : void{
		$this->selectedPrefix = $index;
	}

	/** @return Prefix[] */
	public function getPrefixes() : array{
		return $this->prefixes;
	}

	public function addPrefix(Prefix $prefix) : void{
		$this->prefixes[] = $prefix;
	}

	public function removePrefix(Prefix $prefix) : void{
		unset($this->prefixes[array_search($prefix, $this->prefixes, true)]);
		$this->prefixes = array_values($this->prefixes);
	}

	public function setCustomName(string $customName) : void{
		$this->customName = $customName;
	}
}
