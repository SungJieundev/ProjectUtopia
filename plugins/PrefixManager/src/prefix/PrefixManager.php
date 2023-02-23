<?php

declare(strict_types=1);

namespace alvin0319\PrefixManager\prefix;

use alvin0319\PrefixManager\Loader;
use pocketmine\utils\AssumptionFailedError;
use SOFe\AwaitGenerator\Await;
use function count;

final class PrefixManager{

	/** @var Prefix[] */
	private array $prefixes = [];

	private Prefix $defaultPrefix;

	public function addPrefix(string $prefix, int $id = -1) : void{
		if($id !== -1){
			$this->prefixes[$id] = new Prefix($id, $prefix);
			return;
		}
		Await::f2c(function() use ($prefix) : \Generator{
			yield from Loader::$database->createPrefix($prefix);
			$rows = yield from Loader::$database->getPrefix($prefix);
			if(count($rows) < 1){
				throw new AssumptionFailedError("Failed to create prefix");
			}
			$this->prefixes[$rows[0]["id"]] = new Prefix($rows[0]["id"], $rows[0]["prefix"]);
		});
	}

	public function removePrefix(int $id) : void{
		if(!isset($this->prefixes[$id])){
			return;
		}
		Await::f2c(function() use ($id) : \Generator{
			$prefix = $this->prefixes[$id];
			yield from Loader::$database->deletePrefix($prefix->prefix);
			unset($this->prefixes[$id]);
		});
	}

	/** @return Prefix[] */
	public function getPrefixes() : array{
		return $this->prefixes;
	}

	public function getPrefix(int $id) : ?Prefix{
		return $this->prefixes[$id] ?? null;
	}

	public function setDefaultPrefix(Prefix $prefix) : void{
		$this->defaultPrefix = $prefix;
	}

	public function getDefaultPrefix() : Prefix{
		return $this->defaultPrefix;
	}

	public function getPrefixByName(string $name) : ?Prefix{
		foreach($this->prefixes as $prefix){
			if($prefix->prefix === $name){
				return $prefix;
			}
		}
		return null;
	}
}
