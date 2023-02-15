<?php

declare(strict_types=1);

namespace alvin0319\Market\market;

use alvin0319\EconomyAPI\currency\Currency;
use alvin0319\EconomyAPI\EconomyAPI;
use alvin0319\Market\Loader;
use pocketmine\item\Item;
use pocketmine\utils\Filesystem;
use pocketmine\utils\Utils;
use Symfony\Component\Filesystem\Path;
use function file_exists;
use function file_get_contents;
use function json_decode;
use function json_encode;
use const JSON_THROW_ON_ERROR;

final class MarketManager{

	/** @var Market[] */
	private array $markets = [];

	public function __construct(private readonly Loader $plugin){
		if(file_exists($file = Path::join($this->plugin->getDataFolder(), "markets.json"))){
			$data = json_decode(Utils::assumeNotFalse(file_get_contents($file)), true, 512, JSON_THROW_ON_ERROR);
			foreach($data as $key => $value){
				$this->markets[$key] = Market::jsonDeserialize($value);
			}
		}
	}

	public function getMarketById(int $Id) : ?Market{
		return $this->markets[$Id] ?? null;
	}

	public function getMarketByItem(Item $item) : ?Market{
		foreach($this->markets as $id => $market){
			if($market->getItem()->equals($item, true, true)){
				return $market;
			}
		}
		return null;
	}

	public function createMarket(Item $item, int $buyPrice, int $sellPrice, ?Currency $currency = null) : Market{
		$currency ??= EconomyAPI::getInstance()->getDefaultCurrency();
		$id = 0;
		while(isset($this->markets[$id])){
			++$id;
		}
		return $this->markets[$id] = new Market($id, $item, $buyPrice, $sellPrice, $currency);
	}

	public function save() : void{
		$data = [];
		foreach($this->markets as $id => $market){
			$data[$id] = $market->jsonSerialize();
		}
		Filesystem::safeFilePutContents(Path::join($this->plugin->getDataFolder(), "markets.json"), Utils::assumeNotFalse(json_encode($data, JSON_THROW_ON_ERROR)));
	}
}
