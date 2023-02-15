<?php

declare(strict_types=1);

namespace alvin0319\Market\market;

use alvin0319\EconomyAPI\currency\Currency;
use alvin0319\EconomyAPI\EconomyAPI;
use alvin0319\EconomyAPI\util\Transaction;
use alvin0319\EconomyAPI\util\TransactionResult;
use alvin0319\EconomyAPI\util\TransactionType;
use alvin0319\Market\Loader;
use JsonSerializable;
use pocketmine\item\Item;
use pocketmine\player\Player;
use function alvin0319\ExtensionPlugin\decodeItem;
use function alvin0319\ExtensionPlugin\encodeItem;
use function sprintf;

final class Market implements JsonSerializable{

	public function __construct(
		private int $id,
		private Item $item,
		private int $buyPrice,
		private int $sellPrice,
		private Currency $currency
	){
	}

	public function getId() : int{
		return $this->id;
	}

	public function getItem() : Item{
		return clone $this->item;
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
		$this->buyPrice = $buyPrice;
	}

	public function setSellPrice(int $sellPrice) : void{
		$this->sellPrice = $sellPrice;
	}

	public function buy(Player $player, int $count = 1) : void{
		if($this->buyPrice < 0){
			$player->sendMessage(Loader::$prefix . "이 아이템은 구매할 수 없습니다.");
			return;
		}

		$price = $this->buyPrice * $count;
		$item = $this->getItem()->setCount($count);
		if(!$player->getInventory()->canAddItem($item)){
			$player->sendMessage(Loader::$prefix . "인벤토리에 공간이 충분하지 않습니다..");
			return;
		}
		$tx = new Transaction($player, $price, TransactionType::REDUCE(), $this->currency);
		$beforeMoney = $tx->getSession()->getMoney($this->currency);
		$result = $tx->execute();
		if(!$result->equals(TransactionResult::SUCCESS())){
			$player->sendMessage(Loader::$prefix . $result->getReason());
			return;
		}
		$afterMoney = $tx->getSession()->getMoney($this->currency);
		$player->getInventory()->addItem($item);
		$player->sendMessage(Loader::$prefix . sprintf("%s을(를) %d개 구매했습니다.", $item->getName(), $count));
		$player->sendMessage(Loader::$prefix . sprintf("구매 전 소지금: %s, 구매 후 소지금: %s, 소비한 금액: %s", $this->currency->format($beforeMoney), $this->currency->format($afterMoney), $this->currency->format($beforeMoney - $afterMoney)));
	}

	public function sell(Player $player, int $count = 1) : void{
		if($this->sellPrice < 0){
			$player->sendMessage(Loader::$prefix . "이 아이템은 판매할 수 없습니다.");
			return;
		}

		$price = $this->sellPrice * $count;
		$item = $this->getItem()->setCount($count);
		if(!$player->getInventory()->contains($item)){
			$player->sendMessage(Loader::$prefix . "인벤토리에 아이템이 충분하지 않습니다..");
			return;
		}
		$tx = new Transaction($player, $price, TransactionType::ADD(), $this->currency);
		$beforeMoney = $tx->getSession()->getMoney($this->currency);
		$result = $tx->execute();
		if(!$result->equals(TransactionResult::SUCCESS())){
			$player->sendMessage(Loader::$prefix . $result->getReason());
			return;
		}
		$afterMoney = $tx->getSession()->getMoney($this->currency);
		$player->getInventory()->removeItem($item);
		$player->sendMessage(Loader::$prefix . sprintf("%s을(를) %d개 판매했습니다.", $item->getName(), $count));
		$player->sendMessage(Loader::$prefix . sprintf("판매 전 소지금: %s, 판매 후 소지금: %s, 획득한 금액: %s", $this->currency->format($beforeMoney), $this->currency->format($afterMoney), $this->currency->format($afterMoney - $beforeMoney)));
	}

	/** @phpstan-return list<string> */
	public function toText(bool $ansi = true, string $originalColor = "§7") : array{
		$buyPrice = $this->buyPrice >= 0 ? $this->currency->format($this->buyPrice, $ansi, $originalColor) . $this->currency->getSymbol() : "§c-";
		$sellPrice = $this->sellPrice >= 0 ? $this->currency->format($this->sellPrice, $ansi, $originalColor) . $this->currency->getSymbol() : "§c-";
		return [$buyPrice, $sellPrice];
	}

	public function setCurrency(?Currency $currency = null) : void{
		$this->currency = $currency ?? EconomyAPI::getInstance()->getDefaultCurrency();
	}

	/** @return array<string, mixed> */
	public function jsonSerialize() : array{
		return [
			"id" => $this->id,
			"item" => encodeItem($this->item),
			"buyPrice" => $this->buyPrice,
			"sellPrice" => $this->sellPrice,
			"currency" => $this->currency->getName()
		];
	}

	/** @param array<string, mixed> $data */
	public static function jsonDeserialize(array $data) : Market{
		return new Market(
			$data["id"],
			decodeItem($data["item"]),
			$data["buyPrice"],
			$data["sellPrice"],
			EconomyAPI::getInstance()->getCurrency($data["currency"]) ?? EconomyAPI::getInstance()->getDefaultCurrency()
		);
	}
}
