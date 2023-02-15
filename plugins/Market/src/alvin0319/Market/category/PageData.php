<?php

declare(strict_types=1);

namespace alvin0319\Market\category;

use alvin0319\Market\Loader;
use alvin0319\Market\market\Market;
use JsonSerializable;

final class PageData implements JsonSerializable{

	/** @var Market[] */
	private array $markets = [];

	/** @param array<int, int> $markets */
	public function __construct(array $markets){
		foreach($markets as $slot => $marketId){
			$market = Loader::getInstance()->getMarketManager()->getMarketById($marketId);
			if($market !== null){
				$this->markets[$slot] = $market;
			}
		}
	}

	public function getMarket(int $slot) : ?Market{
		return $this->markets[$slot] ?? null;
	}

	public function setMarket(int $slot, Market $market) : void{
		$this->markets[$slot] = $market;
	}

	/** @param Market[] $markets */
	public function setMarkets(array $markets) : void{
		$this->markets = $markets;
	}

	/** @return Market[] */
	public function getMarkets() : array{
		return $this->markets;
	}

	public function hasMarket(Market $market) : bool{
		foreach($this->markets as $slot => $other){
			if($market->getId() === $other->getId()){
				return true;
			}
		}
		return false;
	}

	/** @return array<int, int> */
	public function jsonSerialize() : array{
		$markets = [];
		foreach($this->markets as $slot => $market){
			$markets[$slot] = $market->getId();
		}
		return $markets;
	}
}
