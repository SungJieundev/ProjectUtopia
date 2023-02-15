<?php

declare(strict_types=1);

namespace alvin0319\Market\market;

final class MarketManager{

	/** @var Market[] */
	private array $markets = [];

	public function __construct(){

	}

	/** @return Market[] */
	public function getMarkets() : array{
		return $this->markets;
	}
}