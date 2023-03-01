<?php

declare(strict_types=1);

namespace alvin0319\Island;

use alvin0319\EconomyAPI\EconomyAPI;
use alvin0319\Island\currency\CurrencyIslandPoint;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\SingletonTrait;

final class Loader extends PluginBase{
	use SingletonTrait;

	protected function onLoad() : void{
		self::setInstance($this);
	}

	protected function onEnable() : void{
		EconomyAPI::getInstance()->registerCurrency(new CurrencyIslandPoint());
	}
}
