<?php

declare(strict_types=1);

namespace alvin0319\Market\listener;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\inventory\CallbackInventoryListener;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;

final class PlayerListener implements Listener{

	public function onPlayerJoin(PlayerJoinEvent $event) : void{
		$player = $event->getPlayer();
		$listener = new CallbackInventoryListener(
			function(Inventory $inventory, int $slot, Item $item) : void{
				$newItem = $inventory->getItem($slot);
				if($newItem->isNull()){
					return;
				}
				$nbt = $newItem->getNamedTag();
				if(
					$nbt->getTag(InventoryListener::TAG_EDIT_MARKETS) !== null ||
					$nbt->getTag(InventoryListener::TAG_MARKET) !== null ||
					$nbt->getTag(InventoryListener::TAG_NEXT_PAGE) !== null ||
					$nbt->getTag(InventoryListener::TAG_PREVIOUS_PAGE) !== null ||
					$nbt->getTag(InventoryListener::TAG_SELLALL) !== null ||
					$nbt->getTag(InventoryListener::TAG_INFO) !== null
				){
					$inventory->setItem($slot, VanillaItems::AIR());
				}
			},
			function(Inventory $inventory, array $changedContent) : void{
//				foreach($changedContent as $slot => $_){
//					$newItem = $inventory->getItem($slot);
//					if($newItem->isNull()){
//						continue;
//					}
//					$nbt = $newItem->getNamedTag();
//					if(
//						$nbt->getTag("auctionId") !== null ||
//						$nbt->getTag("myAuctions") !== null ||
//						$nbt->getTag("mode") !== null ||
//						$nbt->getTag("previousPage") !== null ||
//						$nbt->getTag("refresh") !== null ||
//						$nbt->getTag("nextPage") !== null ||
//						$nbt->getTag("search") !== null ||
//						$nbt->getTag("claim") !== null
//					){
//						$inventory->setItem($slot, VanillaItems::AIR());
//					}
//				}
			}
		);
		$player->getInventory()->getListeners()->add($listener);
		$player->getArmorInventory()->getListeners()->add($listener);
		$player->getOffHandInventory()->getListeners()->add($listener);
	}
}
