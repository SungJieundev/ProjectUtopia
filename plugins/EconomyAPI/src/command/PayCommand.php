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
use SOFe\AwaitGenerator\Await;
use function array_shift;
use function count;
use function is_numeric;
use function strtolower;

final class PayCommand extends BaseEconomyCommand{

	public function __construct(){
		parent::__construct("지불", "다른 플레이어에게 돈을 지불합니다.", "", ["pay"]);
		$this->setPermission("economyapi.command.pay");
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args) : void{
		if(!$this->testPermission($sender)){
			return;
		}
		if(!$sender instanceof Player){
			return;
		}
		if(count($args) < 2){
			$sender->sendMessage(EconomyAPI::$prefix . "사용법: /$commandLabel <플레이어> <금액> [통화]");
			return;
		}
		$player = array_shift($args);
		if(strtolower($player) === strtolower($sender->getName())){
			$sender->sendMessage(EconomyAPI::$prefix . "자기 자신에게는 지불할 수 없습니다.");
			return;
		}
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
		$session = $this->plugin->getSession($sender);
		if($session === null || !$session->isLoaded()){
			$sender->sendMessage(EconomyAPI::$prefix . "잠시 후 다시 시도해주세요.");
			return;
		}
		$playerSession = $this->plugin->getSession($player);
		if($playerSession === null){
			Await::f2c(function() use ($sender, $session, $player, $currency, $amount) : \Generator{
				/** @var EconomySession|null $playerSession */
				$playerSession = yield from $this->plugin->createSession($player);
				if($playerSession === null){
					$sender->sendMessage(EconomyAPI::$prefix . "플레이어를 찾을 수 없습니다.");
					return;
				}
				if(!$playerSession->isLoaded()){
					$playerSession->queueClosure(function() use ($session, $sender, $playerSession, $amount, $currency) : void{
						$this->doTransaction($session, $playerSession, $sender, $amount, $currency);
					});
				}else{
					$this->doTransaction($session, $playerSession, $sender, $amount, $currency);
				}
			});
			return;
		}
		$this->doTransaction($session, $playerSession, $sender, $amount, $currency);
	}

	private function doTransaction(EconomySession $session, EconomySession $playerSession, Player $sender, int $amount, Currency $currency) : void{
		$tx = new Transaction($sender, $amount, TransactionType::PAY(), $currency, $playerSession->getName());
		$result = $tx->execute();
		if(!$result->equals(TransactionResult::SUCCESS())){
			$sender->sendMessage(EconomyAPI::$prefix . $result->getReason());
			return;
		}
		$sender->sendMessage(EconomyAPI::$prefix . $playerSession->getName() . "님에게 " . $currency->format($amount) . "을(를) 지불했습니다.");
		if(($target = $playerSession->getPlayer()) !== null){
			$target->sendMessage(EconomyAPI::$prefix . $session->getName() . "님이 " . $currency->format($amount) . "을(를) 지불했습니다.");
		}
	}
}