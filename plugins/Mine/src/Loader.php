<?php

declare(strict_types=1);

namespace alvin0319\Mine;

use pocketmine\block\Block;
use pocketmine\block\BlockTypeIds;
use pocketmine\block\VanillaBlocks;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\EventPriority;
use pocketmine\math\Facing;
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\ClosureTask;
use pocketmine\utils\SingletonTrait;
use function random_int;

final class Loader extends PluginBase{
	use SingletonTrait;

	/** @var true[] */
	public const ORE_IDS = [
		BlockTypeIds::STONE => true,
		BlockTypeIds::COBBLESTONE => true,
		BlockTypeIds::COAL_ORE => true,
		BlockTypeIds::IRON_ORE => true,
		BlockTypeIds::GOLD_ORE => true,
		BlockTypeIds::DIAMOND_ORE => true,
		BlockTypeIds::EMERALD_ORE => true
	];

	protected function onLoad() : void{
		self::setInstance($this);
	}

	protected function onEnable() : void{
		$this->getServer()->getPluginManager()->registerEvent(BlockBreakEvent::class, $this->onBlockBreak(...), EventPriority::NORMAL, $this);
		$this->getServer()->getPluginManager()->registerEvent(BlockPlaceEvent::class, $this->onBlockPlace(...), EventPriority::NORMAL, $this);
	}

	private function onBlockBreak(BlockBreakEvent $event) : void{
		$block = $event->getBlock();
		if(!isset(self::ORE_IDS[$block->getTypeId()])){
			return;
		}
		if($block->getSide(Facing::DOWN)->getTypeId() !== BlockTypeIds::END_STONE){
			return;
		}
		$this->getScheduler()->scheduleTask(new ClosureTask(function() use ($block) : void{
			$block->getPosition()->getWorld()->setBlock($block->getPosition(), $this->getRandomOreBlock());
		}));
	}

	private function onBlockPlace(BlockPlaceEvent $event) : void{
		$transaction = $event->getTransaction();
		foreach($transaction->getBlocks() as [$x, $y, $z, $block]){
			if($block->getTypeId() === BlockTypeIds::END_STONE){
				$transaction->addBlockAt($x, $y + 1, $z, $this->getRandomOreBlock());
			}
		}
	}

	private function getRandomOreBlock() : Block{
		$r = random_int(0, 150);
		return match (true) {
			$r < 50 => VanillaBlocks::COAL_ORE(),
			$r >= 50 && $r < 55 => VanillaBlocks::IRON_ORE(),
			$r >= 55 && $r < 65 => VanillaBlocks::GOLD_ORE(),
			$r >= 65 && $r < 75 => VanillaBlocks::DIAMOND_ORE(),
			$r >= 75 && $r < 80 => VanillaBlocks::EMERALD_ORE(),
			default => VanillaBlocks::STONE()
		};
	}
}