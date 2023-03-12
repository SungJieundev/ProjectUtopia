<?php

declare(strict_types=1);

namespace alvin0319\Warn;

use alvin0319\SessionManager\Loader as SessionManager;
use alvin0319\SessionManager\session\BaseSession;
use alvin0319\Warn\command\WarnCommand;
use alvin0319\Warn\session\PlayerWarnSession;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\SingletonTrait;
use poggit\libasynql\DataConnector;
use poggit\libasynql\libasynql;
use function strtolower;

final class Loader extends PluginBase{
	use SingletonTrait;

	public static string $prefix = "§b§l[알림] §r§7";

	private DataConnector $connector;

	public static Database $database;

	/** @var \WeakReference<PlayerWarnSession>[] */
	private array $sessions = [];

	protected function onLoad() : void{
		self::setInstance($this);
	}

	protected function onEnable() : void{
		$this->connector = libasynql::create($this, $this->getConfig()->get("database"), [
			"mysql" => "mysql.sql",
			"sqlite" => "sqlite.sql"
		]);
		self::$database = new Database($this->connector);
		SessionManager::getInstance()->registerSessionLoader($this->createSession(...), function(BaseSession $session) : void{
			$player = $session->getPlayer();
			if($player !== null && $session instanceof PlayerWarnSession){
				if($session->isBanned()){
					$player->kick("경고 수 초과로 밴 처리 되었습니다.\n\n총 경고 수: " . $session->getWarnCount() . "회");
				}
			}
		});
		$this->getServer()->getCommandMap()->register("warn", new WarnCommand());
	}

	protected function onDisable() : void{
		$this->connector->waitAll();
		$this->connector->close();
	}

	public function createSession(string $name, ?Player $player = null, bool $createIfNotExists = false) : \Generator{
		$rows = yield from self::$database->getWarns($name);
		$session = new PlayerWarnSession($name, $player, $rows);
		$this->sessions[strtolower($name)] = \WeakReference::create($session);
		return $session;
	}

	public function getSession(Player|string $player) : ?PlayerWarnSession{
		return ($this->sessions[strtolower($player instanceof Player ? $player->getName() : $player)] ?? null)?->get();
	}

	public function removeSession(string $name) : void{
		if(isset($this->sessions[strtolower($name)])){
			unset($this->sessions[strtolower($name)]);
		}
	}
}
