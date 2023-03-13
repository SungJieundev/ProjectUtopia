<?php

declare(strict_types=1);

namespace alvin0319\Weather;

use alvin0319\Weather\season\Weather;
use pocketmine\event\entity\EntityTeleportEvent;
use pocketmine\event\EventPriority;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\SingletonTrait;

final class Loader extends PluginBase{
	use SingletonTrait;

	protected function onLoad() : void{
		self::setInstance($this);
	}

	protected function onEnable() : void{
		$this->getServer()->getPluginManager()->registerEvent(EntityTeleportEvent::class, function(EntityTeleportEvent $event) : void{
			$player = $event->getEntity();
			if(!$player instanceof Player){
				return;
			}
			$from = $event->getFrom();
			$to = $event->getTo();
			if($from->getWorld()->getId() !== $to->getWorld()->getId()){
				return;
			}
			Weather::clearWeather($player);
		}, EventPriority::NORMAL, $this);
	}
}
