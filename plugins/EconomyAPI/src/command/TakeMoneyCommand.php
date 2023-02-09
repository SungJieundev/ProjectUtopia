<?php

declare(strict_types=1);

namespace alvin0319\EconomyAPI\command;

use alvin0319\EconomyAPI\currency\Currency;
use alvin0319\EconomyAPI\EconomyAPI;
use alvin0319\EconomyAPI\session\EconomySession;
use alvin0319\EconomyAPI\util\Transaction;
use alvin0319\EconomyAPI\util\TransactionResult;
use alvin0319\EconomyAPI\util\TransactionType;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\AssumptionFailedError;
use SOFe\AwaitGenerator\Await;
use function array_shift;
use function count;
use function is_numeric;

final class TakeMoneyCommand extends BaseEconomyCommand{
	public function __construct(){
		parent::__construct("돈뺏기", "특정 플레이어에게 돈을 뺏습니다.", "", ["takemoney"]);
		$this->setPermission("economyapi.command.takemoney");
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args) : void{
		if(!$this->testPermission($sender)){
			return;
		}
		if(count($args) < 2){
			$sender->sendMessage(EconomyAPI::$prefix . "사용법: /$commandLabel <플레이어> <금액> [통화]");
			return;
		}
		$player = array_shift($args);
		$amount = array_shift($args);
		if(!is_numeric($amount) || ($amount = (int) $amount) < 1){
			$sender->sendMessage(EconomyAPI::$prefix . "금액은 1 이상의 정수여야 합니다.");
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
		$targetSession = $this->plugin->getSession($player);
		if($targetSession === null){
			Await::f2c(function() use ($sender, $player, $amount, $currency) : \Generator{
				/** @var EconomySession|null $playerSession */
				$playerSession = yield from $this->plugin->createSession($player);
				if($playerSession === null){
					$sender->sendMessage(EconomyAPI::$prefix . "플레이어를 찾을 수 없습니다.");
					return;
				}
				if(!$playerSession->isLoaded()){
					$playerSession->queueClosure(function() use ($sender, $player, $amount, $currency) : void{
						$this->doTransaction($sender, $player, $amount, $currency);
					});
				}else{
					$this->doTransaction($sender, $player, $amount, $currency);
				}
			});
			return;
		}
		$this->doTransaction($sender, $targetSession->getPlayer(), $amount, $currency);
	}

	private function doTransaction(CommandSender $sender, Player|string $player, int $amount, Currency $currency) : void{
		$tx = new Transaction($player, $amount, TransactionType::ADD(), $currency);
		$result = $tx->execute();
		if(!$result->equals(TransactionResult::SUCCESS())){
			$sender->sendMessage(EconomyAPI::$prefix . $result->getReason());
		}else{
			$sender->sendMessage(EconomyAPI::$prefix . $player . "님에게 " . $currency->format($amount) . "을(를) 지급했습니다.");
		}
	}
}