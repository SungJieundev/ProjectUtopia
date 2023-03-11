<?php

declare(strict_types=1);

namespace alvin0319\Island;

use alvin0319\EconomyAPI\EconomyAPI;
use alvin0319\Island\currency\CurrencyIslandPoint;
use alvin0319\Island\island\Island;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\SingletonTrait;
use Symfony\Component\Filesystem\Path;
use function is_dir;
use function mkdir;

final class Loader extends PluginBase{
	use SingletonTrait;

	protected function onLoad() : void{
		self::setInstance($this);
	}

	protected function onEnable() : void{
		if(!is_dir($dir = Path::join($this->getServer()->getDataPath(), "worlds", Island::WORLD_ISLAND_PATH))){
			mkdir($dir);
		}
		EconomyAPI::getInstance()->registerCurrency(new CurrencyIslandPoint());
	}
}
