<?php

declare(strict_types=1);

namespace alvin0319\Warn\command;

use alvin0319\Warn\Loader;
use alvin0319\Warn\session\PlayerWarnSession;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\PluginOwned;
use pocketmine\plugin\PluginOwnedTrait;
use pocketmine\utils\Utils;
use SOFe\AwaitGenerator\Await;
use function alvin0319\ExtensionPlugin\assumeNotNull;
use function array_shift;
use function count;
use function implode;
use function is_numeric;
use function trim;

final class WarnCommand extends Command implements PluginOwned{
	use PluginOwnedTrait;

	public function __construct(){
		parent::__construct("경고", "경고를 관리합니다.");
		$this->setPermission("warn.command");
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
		if(count($args) < 1){
			$sender->sendMessage(Loader::$prefix . "/경고 확인 <플레이어> - 플레이어의 경고를 확인합니다.");
			$sender->sendMessage(Loader::$prefix . "/경고 내정보 - 자신의 경고를 확인합니다.");
			if($sender->hasPermission("warn.command.manage")){
				$sender->sendMessage(Loader::$prefix . "/경고 추가 <플레이어> <회수> <사유> - 플레이어에게 경고를 추가합니다.");
				$sender->sendMessage(Loader::$prefix . "/경고 제거 <플레이어> <경고번호> - 플레이어의 경고를 제거합니다.");
			}
			return;
		}
		switch(assumeNotNull(array_shift($args))){
			case "확인":
				if(count($args) < 1){
					$sender->sendMessage(Loader::$prefix . "/경고 확인 <플레이어> - 플레이어의 경고를 확인합니다.");
					return;
				}
				$name = assumeNotNull(array_shift($args));
				if(trim($name) === ""){
					return;
				}
				$session = Loader::getInstance()->getSession($name);
				$callback = function(PlayerWarnSession $session) use ($name, $sender) : void{
					if(!$sender->isConnected()){
						return;
					}
					$sender->sendMessage(Loader::$prefix . "§e{$name}§7님의 경고: ");
					$warns = $session->getWarns();
					if(count($warns) === 0){
						$sender->sendMessage(Loader::$prefix . "지급된 경고가 없습니다.");
						return;
					}
					foreach($warns as $warnData){
						$sender->sendMessage("§b§l[$warnData->index] §r§7회수: $warnData->amount, 사유: $warnData->reason, 지급 시각: $warnData->time");
					}
				};
				if($session === null){
					Await::f2c(function() use ($name, $callback) : \Generator{
						$session = yield from Loader::getInstance()->createSession($name);
						$this->operate($session, $callback);
					});
					return;
				}
				$this->operate($session, $callback);
				break;
			case "내정보":
				$session = Loader::getInstance()->getSession($sender->getName());
				if($session === null){
					return;
				}
				$sender->sendMessage(Loader::$prefix . "§e{$sender->getName()}§7님의 경고: ");
				$warns = $session->getWarns();
				if(count($warns) === 0){
					$sender->sendMessage(Loader::$prefix . "지급된 경고가 없습니다.");
					return;
				}
				foreach($warns as $warnData){
					$sender->sendMessage("§b§l[$warnData->index] §r§7회수: $warnData->amount, 사유: $warnData->reason, 지급 시각: $warnData->time");
				}
				break;
			case "추가":
				if(!$this->testPermission($sender, "warn.command.manage")){
					return;
				}
				if(count($args) < 3){
					$sender->sendMessage(Loader::$prefix . "/경고 추가 <플레이어> <회수> <사유> - 플레이어에게 경고를 추가합니다.");
					return;
				}
				$name = assumeNotNull(array_shift($args));
				if(trim($name) === ""){
					return;
				}
				$amount = assumeNotNull(array_shift($args));
				if(!is_numeric($amount) || ($amount = (int) $amount) < 1){
					$sender->sendMessage(Loader::$prefix . "회수는 1 이상의 정수여야 합니다.");
					return;
				}
				$reason = implode(" ", $args);
				$session = Loader::getInstance()->getSession($name);
				$callback = function(PlayerWarnSession $session) use ($name, $amount, $reason, $sender) : void{
					if(!$sender->isConnected()){
						return;
					}
					$session->addWarn($reason, $amount);
					$sender->sendMessage(Loader::$prefix . "§e{$name}§7님에게 §e{$amount}§7개의 경고를 추가했습니다.");
				};
				if($session === null){
					Await::f2c(function() use ($name, $callback) : \Generator{
						$session = yield from Loader::getInstance()->createSession($name);
						$this->operate($session, $callback);
					});
					return;
				}
				$this->operate($session, $callback);
				break;
			case "제거":
				if(!$this->testPermission($sender, "warn.command.manage")){
					return;
				}
				if(count($args) < 2){
					$sender->sendMessage(Loader::$prefix . "/경고 제거 <플레이어> <경고번호> - 플레이어의 경고를 제거합니다.");
					return;
				}
				$name = assumeNotNull(array_shift($args));
				if(trim($name) === ""){
					return;
				}
				$index = assumeNotNull(array_shift($args));
				if(!is_numeric($index) || ($index = (int) $index) < 1){
					$sender->sendMessage(Loader::$prefix . "경고번호는 1 이상의 정수여야 합니다.");
					return;
				}
				$session = Loader::getInstance()->getSession($name);
				$callback = function(PlayerWarnSession $session) use ($name, $index, $sender) : void{
					if(!$sender->isConnected()){
						return;
					}
					if(!$session->hasWarnIndex($index)){
						$sender->sendMessage(Loader::$prefix . "§e{$name}§7님의 경고 §e{$index}§7번을 찾을 수 없습니다.");
						return;
					}
					$sender->sendMessage(Loader::$prefix . "§e{$name}§7님의 §e{$index}§7번 경고를 제거했습니다.");
				};
				if($session === null){
					Await::f2c(function() use ($name, $callback) : \Generator{
						$session = yield from Loader::getInstance()->createSession($name);
						$this->operate($session, $callback);
					});
					return;
				}
				$this->operate($session, $callback);
				break;
			default:
				$sender->sendMessage(Loader::$prefix . "/경고 <플레이어> - 플레이어의 경고를 확인합니다.");
				$sender->sendMessage(Loader::$prefix . "/경고 내정보 - 자신의 경고를 확인합니다.");
				if($this->testPermission($sender, "warn.command.manage")){
					$sender->sendMessage(Loader::$prefix . "/경고 추가 <플레이어> <회수> <사유> - 플레이어에게 경고를 추가합니다.");
					$sender->sendMessage(Loader::$prefix . "/경고 제거 <플레이어> <경고번호> - 플레이어의 경고를 제거합니다.");
				}
		}
	}

	private function operate(PlayerWarnSession $session, \Closure $callback) : void{
		Utils::validateCallableSignature(function(PlayerWarnSession $session) : void{ }, $callback);
		$callback($session);
	}
}
