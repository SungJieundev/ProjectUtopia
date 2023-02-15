<?php

declare(strict_types=1);

namespace alvin0319\Market\market;

use alvin0319\EconomyAPI\currency\Currency;
use pocketmine\item\Item;

final class Market{

	public function __construct(
		public readonly int $id,
		public readonly Item $item,
		private int $buyPrice,
		private int $sellPrice,
		private Currency $currency
	){
	}

	public function getBuyPrice() : int{
		return $this->buyPrice;
	}

	public function getSellPrice() : int{
		return $this->sellPrice;
	}

	public function getCurrency() : Currency{
		return $this->currency;
	}

	public function setBuyPrice(int $buyPrice) : void{
		$this->buyPrice = $buyPrice < 0 ? -1 : $buyPrice;
	}

	public function setSellPrice(int $sellPrice) : void{
		$this->sellPrice = $sellPrice < 0 ? -1 : $sellPrice;
	}

	public function setCurrency(Currency $currency) : void{
		$this->currency = $currency;
	}
}