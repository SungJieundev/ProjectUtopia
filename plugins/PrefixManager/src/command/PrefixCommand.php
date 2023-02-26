<?php

declare(strict_types=1);

namespace alvin0319\PrefixManager\command;

use alvin0319\PrefixManager\form\AdminForm;
use alvin0319\PrefixManager\form\PrefixMainForm;
use alvin0319\PrefixManager\Loader;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\PluginOwned;
use pocketmine\plugin\PluginOwnedTrait;

final class PrefixCommand extends Command implements PluginOwned{
	use PluginOwnedTrait;

	public function __construct(){
		$this->owningPlugin = Loader::getInstance();
		parent::__construct("칭호", "칭호 UI를 엽니다.");
		$this->setPermission("prefixmanager.command");
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args) : void{
		if(!$this->testPermission($sender)){
			return;
		}
		if(!$sender instanceof Player){
			$sender->sendMessage(Loader::$prefix . "인게임에서 사용해주세요.");
			return;
		}
		$session = Loader::getInstance()->getSession($sender);
		if($session === null || !$session->isLoaded()){
			$sender->sendMessage(Loader::$prefix . "아직 데이터가 로드되지 않았습니다.");
			return;
		}
		if(!$sender->hasPermission("prefixmanager.command.manage")){
			$sender->sendForm(new PrefixMainForm($session));
			return;
		}
		$sender->sendForm(new AdminForm($session));
	}
}
