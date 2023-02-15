<?php

declare(strict_types=1);

namespace alvin0319\Market;

use alvin0319\Market\category\CategoryManager;
use alvin0319\Market\command\BuyPriceCommand;
use alvin0319\Market\command\MarketAdminCommand;
use alvin0319\Market\command\MarketCommand;
use alvin0319\Market\command\SellPriceCommand;
use alvin0319\Market\listener\PlayerListener;
use alvin0319\Market\market\MarketManager;
use muqsit\invmenu\InvMenuHandler;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\SingletonTrait;

final class Loader extends PluginBase{
	use SingletonTrait;

	public static string $prefix = "§b§l[알림] §r§7";

	private MarketManager $marketManager;

	private CategoryManager $categoryManager;

	private bool $enabled = false;

	protected function onLoad() : void{
		self::setInstance($this);
	}

	protected function onEnable() : void{
		if(!InvMenuHandler::isRegistered()){
			InvMenuHandler::register($this);
		}
		$this->marketManager = new MarketManager($this);
		$this->categoryManager = new CategoryManager($this);

		$this->getServer()->getPluginManager()->registerEvents(new PlayerListener(), $this);

		$this->getServer()->getCommandMap()->registerAll("market", [
			new MarketCommand(),
			new MarketAdminCommand(),
			new BuyPriceCommand(),
			new SellPriceCommand()
		]);
		$this->enabled = true;
	}

	protected function onDisable() : void{
		if(!$this->enabled){
			return;
		}
		$this->marketManager->save();
		$this->categoryManager->save();
	}

	public function getMarketManager() : MarketManager{
		return $this->marketManager;
	}

	public function getCategoryManager() : CategoryManager{
		return $this->categoryManager;
	}
}
