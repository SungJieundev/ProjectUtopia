<?php

declare(strict_types=1);

namespace alvin0319\PrefixManager;

use alvin0319\PrefixManager\chat\PrefixChatFormatter;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\event\player\PlayerQuitEvent;

final readonly class EventListener implements Listener{

	public function __construct(private Loader $plugin){ }

	public function onPlayerLogin(PlayerLoginEvent $event) : void{
		$player = $event->getPlayer();
		$this->plugin->createSession($player);
	}

	public function onPlayerQuit(PlayerQuitEvent $event) : void{
		$player = $event->getPlayer();
		$this->plugin->removeSession($player);
	}

	/** @priority LOWEST */
	public function onPlayerChat(PlayerChatEvent $event) : void{
		$player = $event->getPlayer();

		$prefixSession = $this->plugin->getSession($player);
		if($prefixSession === null){
			$event->cancel();
			return;
		}
		if(!$prefixSession->isLoaded()){
			$event->cancel();
			return;
		}
		if(!isset($prefixSession->formatter)){
			$prefixSession->formatter = new PrefixChatFormatter($player, $prefixSession);
		}
		$event->setFormatter($prefixSession->formatter);
	}
}
