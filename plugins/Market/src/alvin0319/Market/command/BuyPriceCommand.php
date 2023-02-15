<?php

declare(strict_types=1);

namespace alvin0319\Market\command;

use alvin0319\EconomyAPI\EconomyAPI;
use alvin0319\Market\Loader;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\lang\Translatable;
use pocketmine\player\Player;
use pocketmine\plugin\PluginOwned;
use pocketmine\plugin\PluginOwnedTrait;
use function count;
use function is_numeric;

final class BuyPriceCommand extends Command implements PluginOwned{
	use PluginOwnedTrait;

	public function __construct(){
		parent::__construct("buyprice", "Sets the buy price of item", "/buyprice <price> <currency>", ["bp"]);
		$this->setPermission("market.command.buyprice");
		$this->owningPlugin = Loader::getInstance();
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args) : void{
		if(!$this->testPermission($sender)){
			return;
		}
		if(!$sender instanceof Player){
			$sender->sendMessage(Loader::$prefix . "인게임에서만 사용할 수 있습니다.");
			return;
		}
		if(count($args) < 2){
			$sender->sendMessage(Loader::$prefix . "사용법: " . (($usage = $this->getUsage()) instanceof Translatable ? $usage->getText() : $usage));
			return;
		}
		$currency = EconomyAPI::getInstance()->getDefaultCurrency();
		[$price, $currencyName] = $args;
		if(!is_numeric($price) || ($price = (int) $price) < -1){
			$sender->sendMessage(Loader::$prefix . "구매가는 정수여야 합니다.");
			return;
		}
		if(EconomyAPI::getInstance()->getCurrency($currencyName) === null){
			$sender->sendMessage(Loader::$prefix . "잘못된 통화입니다.");
			return;
		}
		$item = $sender->getInventory()->getItemInHand();
		if($item->isNull()){
			$sender->sendMessage(Loader::$prefix . "공기의 구매가를 설정할 수 없습니다.");
			return;
		}
		$currency = EconomyAPI::getInstance()->getCurrency($currencyName);

		$market = Loader::getInstance()->getMarketManager()->getMarketByItem($item) ?? Loader::getInstance()->getMarketManager()->createMarket($item, $price, -1, $currency);
		$market->setBuyPrice($price);
		$market->setCurrency($currency);
		$sender->sendMessage(Loader::$prefix . "{$item->getName()}의 판매가를 {$currency->format($price)}으(로) 설정했습니다.");
	}
}
