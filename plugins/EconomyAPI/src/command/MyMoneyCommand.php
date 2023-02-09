<?php

declare(strict_types=1);

namespace alvin0319\EconomyAPI\command;

use alvin0319\EconomyAPI\currency\Currency;
use alvin0319\EconomyAPI\EconomyAPI;
use alvin0319\EconomyAPI\session\EconomySession;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\AssumptionFailedError;
use function array_shift;
use function count;

final class MyMoneyCommand extends BaseEconomyCommand{
	public function __construct(){
		parent::__construct("돈", "내 돈을 확인합니다.", "", ["내돈", "mymoney"]);
		$this->setPermission("economyapi.command.mymoney");
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args) : void{
		if(!$this->testPermission($sender)){
			return;
		}
		if(!$sender instanceof Player){
			$sender->sendMessage(EconomyAPI::$prefix . "인게임에서만 사용할 수 있습니다.");
			return;
		}
		$session = $this->plugin->getSession($sender);
		if($session === null){
			$sender->sendMessage(EconomyAPI::$prefix . "잠시 후 다시 시도해주세요.");
			return;
		}
		$currency = $this->plugin->getDefaultCurrency();
		if(count($args) > 0){
			$currencyName = array_shift($args);
			if($this->plugin->getCurrency($currencyName) !== null){
				$currency = $this->plugin->getCurrency($currencyName);
			}
		}
		if($currency === null){
			throw new AssumptionFailedError("Currency is null");
		}
		$session->queueClosure(fn() => $this->sendMyMoneyMessage($sender, $session, $currency));
	}

	private function sendMyMoneyMessage(Player $player, EconomySession $session, Currency $currency) : void{
		$player->sendMessage(EconomyAPI::$prefix . "내 돈: " . $currency->format($session->getMoney($currency)));
	}
}