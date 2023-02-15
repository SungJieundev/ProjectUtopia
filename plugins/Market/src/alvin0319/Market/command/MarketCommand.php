<?php

declare(strict_types=1);

namespace alvin0319\Market\command;

use alvin0319\Market\category\Category;
use alvin0319\Market\form\MarketCategoryListForm;
use alvin0319\Market\listener\InventoryListener;
use alvin0319\Market\Loader;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\PluginOwned;
use pocketmine\plugin\PluginOwnedTrait;
use function array_map;
use function array_values;
use function count;
use function implode;

final class  MarketCommand extends Command implements PluginOwned{
	use PluginOwnedTrait;

	public function __construct(){
		parent::__construct("상점", "상점을 엽니다.", "", ["shop"]);
		$this->owningPlugin = Loader::getInstance();
		$this->setPermission("market.command.open");
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
//			$sender->sendMessage(Loader::$prefix . "Usage: /$commandLabel <category|list>");
			$sender->sendForm(new MarketCategoryListForm(array_values(Loader::getInstance()->getCategoryManager()->getCategories())));
			return;
		}
		$name = implode(" ", $args);
		if($name === "list"){
			$sender->sendMessage(Loader::$prefix . "카테고리: " . implode(
					", ", array_map(
						static fn(Category $category) => $category->getName(),
						Loader::getInstance()->getCategoryManager()->getCategories()
					)
				)
			);
			return;
		}
		$category = Loader::getInstance()->getCategoryManager()->getCategory($name);
		if($category === null){
			$sender->sendMessage(Loader::$prefix . "잘못된 카테고리입니다.");
			return;
		}
		InventoryListener::getInstance()->sendCategory($sender, $category);
	}
}
