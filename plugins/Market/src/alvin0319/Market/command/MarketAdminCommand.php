<?php

declare(strict_types=1);

namespace alvin0319\Market\command;

use alvin0319\Market\Loader;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\PluginOwned;
use pocketmine\plugin\PluginOwnedTrait;
use function array_shift;
use function count;
use function implode;

final class MarketAdminCommand extends Command implements PluginOwned{
	use PluginOwnedTrait;

	public function __construct(){
		parent::__construct("상점관리", "상점을 관리합니다.");
		$this->setPermission("market.command.admin");
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
			$sender->sendMessage(Loader::$prefix . "사용법: /상점관리 <카테고리생성|카테고리제거|목록>");
			return;
		}
		switch(array_shift($args)){
			case "카테고리생성":
				if(count($args) < 1){
					$sender->sendMessage(Loader::$prefix . "사용법: /상점관리 카테고리생성 <이름>");
					return;
				}
				$name = implode(" ", $args);
				if(Loader::getInstance()->getCategoryManager()->getCategory($name) !== null){
					$sender->sendMessage(Loader::$prefix . "카테고리가 이미 존재합니다.");
					return;
				}
				Loader::getInstance()->getCategoryManager()->createCategory($name);
				$sender->sendMessage(Loader::$prefix . "카테고리를 생성했습니다.");
				break;
			case "카테고리제거":
				if(count($args) < 1){
					$sender->sendMessage(Loader::$prefix . "사용법: /상점관리 카테고리제거 <이름>");
					return;
				}
				$name = implode(" ", $args);
				$category = Loader::getInstance()->getCategoryManager()->getCategory($name);
				if($category === null){
					$sender->sendMessage(Loader::$prefix . "잘못된 카테고리입니다.");
					return;
				}
				Loader::getInstance()->getCategoryManager()->removeCategory($category->getName());
				$sender->sendMessage(Loader::$prefix . "카테고리를 제거했습니다.");
				break;
			case "list":
				$sender->sendMessage(Loader::$prefix . "카테고리 목록:");
				foreach(Loader::getInstance()->getCategoryManager()->getCategories() as $category){
					$sender->sendMessage("- " . $category->getName());
				}
				break;
			default:
				$sender->sendMessage(Loader::$prefix . "사용법: /상점관리 <카테고리생성|카테고리제거|목록>");
				break;
		}
	}
}
