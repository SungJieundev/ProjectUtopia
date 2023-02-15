<?php

declare(strict_types=1);

namespace alvin0319\Market;

use pocketmine\plugin\PluginBase;
use pocketmine\utils\SingletonTrait;
use poggit\libasynql\DataConnector;
use poggit\libasynql\libasynql;
use SOFe\AwaitGenerator\Await;

final class Loader extends PluginBase{
	use SingletonTrait;

	public static string $prefix = "§b§l[알림] §r§7";

	private DataConnector $connector;

	public static Database $database;

	protected function onLoad() : void{
		self::setInstance($this);
	}

	protected function onEnable() : void{
		$this->connector = libasynql::create($this, $this->getConfig()->get("database"), [
			"mysql" => "mysql.sql",
			"sqlite" => "sqlite.sql"
		]);
		self::$database = new Database($this->connector);
		Await::g2c(self::$database->init());
	}
}