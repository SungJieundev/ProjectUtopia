<?php

declare(strict_types=1);

namespace alvin0319\Market\form;

use alvin0319\Market\Loader;
use alvin0319\Market\market\Market;
use pocketmine\form\Form;
use pocketmine\item\Item;
use pocketmine\player\Player;
use function count;
use function is_array;
use function is_numeric;

final readonly class MarketBuyForm implements Form{

	public function __construct(private Player $player, private Market $market){ }

	/** @phpstan-return CustomForm */
	public function jsonSerialize() : array{
		$canSell = 0;
		/** @var Item $item */
		foreach($this->player->getInventory()->all($this->market->getItem()) as $slot => $item){
			$canSell += $item->getCount();
		}
		$prices = $this->market->toText(true, "§f");
		$content = "§fBuy: §f" . $prices[0] . "§f\nSell: §f" .
			$prices[1] . "§f\n판매 가능한 개수: §6{$canSell}§f";
		return [
			"type" => "custom_form",
			"title" => "§l{$this->market->getItem()->getName()}",
			"content" => [
				[
					"type" => "label",
					"text" => $content
				],
				[
					"type" => "dropdown",
					"text" => "구매 / 판매",
					"options" => ["구매", "판매"]
				],
				[
					"type" => "input",
					"text" => "구매 또는 판매할 아이템의 개수"
				]
			]
		];
	}

	public function handleResponse(Player $player, $data) : void{
		if(!is_array($data) || count($data) !== 3){
			return;
		}
		[$label, $buyOrSell, $count] = $data;
		if(!is_numeric($count) || ($count = (int) $count) < 1){
			$player->sendMessage(Loader::$prefix . ($buyOrSell === 0 ? "구매" : "판매") . "할 개수를 입력해주세요.");
			return;
		}
		$method = $buyOrSell === 0 ? "buy" : "sell";
		$this->market->{$method}($player, $count);
	}
}
