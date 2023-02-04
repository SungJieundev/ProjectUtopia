<?php

declare(strict_types=1);

namespace alvin0319\EconomyAPI;

use alvin0319\EconomyAPI\currency\Currency;
use alvin0319\EconomyAPI\session\EconomySession;
use alvin0319\SessionManager\Loader;
use alvin0319\SessionManager\session\BaseSession;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\SingletonTrait;
use poggit\libasynql\DataConnector;
use poggit\libasynql\libasynql;
use SOFe\AwaitGenerator\Await;
use function count;

final class EconomyAPI extends PluginBase{
	use SingletonTrait;

	private DataConnector $connector;

	public static Database $database;

	/** @var Currency[] */
	private array $currencies = [];

	protected function onLoad() : void{
		self::setInstance($this);
	}

	protected function onEnable() : void{
		$this->connector = libasynql::create($this, $this->getConfig()->get("database"), [
			"sqlite" => "sqlite.sql",
			"mysql" => "mysql.sql"
		]);
		self::$database = new Database($this->connector);
		Await::g2c(self::$database->economyapiInit());
		$this->connector->waitAll();
		Loader::getInstance()->registerSessionLoader($this, $this->createSession(...), function(BaseSession $session) : void{
			$session->getPlayer()?->sendMessage("Session loaded!");
		});
	}

	public function createSession(string $name, ?Player $player = null) : \Generator{
		$rows = yield from self::$database->economyapiGet($name, 'won');
		if(count($rows) > 0){
		}
		return new EconomySession($name, $player);
	}
}