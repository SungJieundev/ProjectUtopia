<?php

declare(strict_types=1);

namespace alvin0319\ItemCollect;

use pocketmine\math\AxisAlignedBB;

final class CollectArena{

	public function __construct(
		public readonly string $name,
		public readonly AxisAlignedBB $arenaBB,
		private int $regenerationTime = 10
	){
	}

	public function getRegenerationTime() : int{
		return $this->regenerationTime;
	}

	public function setRegenerationTime(int $regenerationTime) : void{
		$this->regenerationTime = $regenerationTime;
	}
}
