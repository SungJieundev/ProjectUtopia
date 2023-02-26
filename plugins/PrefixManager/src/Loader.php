<?php

declare(strict_types=1);

namespace alvin0319\PrefixManager;

use alvin0319\PrefixManager\command\PrefixCommand;
use alvin0319\PrefixManager\session\PrefixSession;
use Generator;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\ClosureTask;
use pocketmine\utils\SingletonTrait;
use poggit\libasynql\DataConnector;
use poggit\libasynql\libasynql;
use SOFe\AwaitGenerator\Await;
use function count;
use function strtolower;

final class Loader extends PluginBase{
	use SingletonTrait;

	public static string $prefix = "§6§l[§f!§6] §r§7";

	public static Database $database;

	private DataConnector $connector;
	/** @var PrefixSession[] */
	private array $sessions = [];

	public const TAG_FREE_PREFIX_ITEM = "free_prefix_item";
	public const TAG_NICKNAME_ITEM = "nickname_item";

	protected function onLoad() : void{
		self::setInstance($this);
	}

	protected function onEnable() : void{
		$this->saveDefaultConfig();
		$this->connector = libasynql::create($this, $this->getConfig()->get("database"), [
			"mysql" => "mysql.sql",
			"sqlite" => "sqlite.sql"
		]);
		self::$database = new Database($this->connector);
		Await::g2c(self::$database->init());
		$this->connector->waitAll();
		$this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
		$this->getScheduler()->scheduleRepeatingTask(new ClosureTask(function() : void{
			foreach($this->sessions as $_ => $session){
				$session->tick();
			}
		}), 20);
		$this->getServer()->getCommandMap()->register("prefixmanager", new PrefixCommand());
	}

	protected function onDisable() : void{
		foreach($this->sessions as $_ => $session){
			$session->save();
		}
		$this->connector->waitAll();
		$this->connector->close();
	}

	public function createSession(Player|string $player) : void{
		$onlinePlayer = null;
		if($player instanceof Player){
			$onlinePlayer = $player;
			$player = $player->getName();
		}
		$player = strtolower($player);
		if(isset($this->sessions[$player])){
			if($this->sessions[$player]->getPlayer() === null && $onlinePlayer !== null){
				$this->sessions[$player]->switchOnline($onlinePlayer);
				$this->getLogger()->debug("Player $player joined while offline session was loaded, marking session as online");
			}
			return;
		}
		$this->sessions[$player] = new PrefixSession($this, $player, $onlinePlayer);
	}

	public function removeSession(Player|string $player) : void{
		if($player instanceof Player){
			$player = $player->getName();
		}
		$player = strtolower($player);
		if(isset($this->sessions[$player])){
			$this->sessions[$player]->save();
			unset($this->sessions[$player]);
		}
	}

	public function hasAccount(string $name) : Generator{
		$rows = yield from self::$database->get(strtolower($name));
		return count($rows) > 0;
	}

	public function getSession(Player|string $player) : ?PrefixSession{
		if($player instanceof Player){
			$player = $player->getName();
		}
		$player = strtolower($player);
		return $this->sessions[$player] ?? null;
	}
}
