<?php

declare(strict_types=1);

namespace alvin0319\PrefixManager;

use alvin0319\PrefixManager\formatter\PrefixChatFormatter;
use alvin0319\PrefixManager\prefix\Prefix;
use alvin0319\PrefixManager\prefix\PrefixManager;
use alvin0319\PrefixManager\session\PrefixSession;
use alvin0319\SessionManager\Loader as SessionManager;
use alvin0319\SessionManager\session\BaseSession;
use pocketmine\event\EventPriority;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\AssumptionFailedError;
use pocketmine\utils\SingletonTrait;
use poggit\libasynql\DataConnector;
use poggit\libasynql\libasynql;
use SOFe\AwaitGenerator\Await;
use SOFe\AwaitStd\AwaitStd;
use function count;
use function strtolower;

final class Loader extends PluginBase{
	use SingletonTrait;

	public static string $prefix = "§b§l[알림] §r§7";

	private DataConnector $connector;

	public static Database $database;

	public static AwaitStd $std;

	public PrefixManager $prefixManager;

	/** @phpstan-var \WeakReference<PrefixSession>[] */
	private array $sessions = [];

	public static Item $freePrefixItem;

	public static Item $nicknameItem;

	protected function onLoad() : void{
		self::setInstance($this);
	}

	protected function onEnable() : void{
		$this->saveDefaultConfig();
		self::$std = AwaitStd::init($this);
		$this->prefixManager = new PrefixManager();
		$this->connector = libasynql::create($this, $this->getConfig()->get("database"), [
			"sqlite" => "sqlite.sql",
			"mysql" => "mysql.sql"
		]);
		self::$database = new Database($this->connector);
		Await::f2c(function() : \Generator{
			yield from self::$database->init();
			yield from self::$database->initSession();
		});
		$this->connector->waitAll();

		Await::f2c(function() : \Generator{
			$defaultPrefix = yield from Loader::$database->getPrefix($this->getDefaultPrefix());
			if(count($defaultPrefix) < 1){
				yield from Loader::$database->createPrefix($this->getDefaultPrefix());
				$rows = yield from Loader::$database->getPrefix($this->getDefaultPrefix());
				if(count($rows) < 1){
					throw new AssumptionFailedError("Failed to create default prefix");
				}
				$this->prefixManager->setDefaultPrefix(new Prefix($rows[0]["id"], $rows[0]["prefix"]));
			}
			$rows = yield from self::$database->getPrefixes();
			if(count($rows) > 0){
				foreach($rows as $row){
					$this->prefixManager->addPrefix($row["prefix"], $row["id"]);
					if($this->getDefaultPrefix() === $row["prefix"]){
						$this->prefixManager->setDefaultPrefix(new Prefix($row["id"], $row["prefix"]));
					}
				}
			}
		});
		SessionManager::getInstance()->registerSessionLoader($this->createSession(...), function(BaseSession $session) : void{ });
		$this->getServer()->getPluginManager()->registerEvent(PlayerChatEvent::class, $this->onPlayerChat(...), EventPriority::NORMAL, $this);
		self::$freePrefixItem = VanillaItems::PAPER()
			->setCustomName("§e§l[소모품] §r§f자유칭호");
		self::$nicknameItem = VanillaItems::PAPER()
			->setCustomName("§e§l[소모품] §r§f닉네임 변경");
	}

	protected function onDisable() : void{
		$this->connector->waitAll();
		$this->connector->close();
	}

	public function getDefaultPrefix() : string{
		return $this->getConfig()->get("default-prefix");
	}

	public function createSession(string $name, ?Player $player = null, bool $createIfNotExists = false) : \Generator{
		$rows = yield from Loader::$database->getSession($name);
		$prefixes = [];
		$customName = "";
		$selectedPrefix = 0;
		if(count($rows) < 1 && !$createIfNotExists){
			return null;
		}
		if(count($rows) > 0){
			if($rows[0]["syncBlocked"] === false){
				$customName = $rows[0]["customName"];
				$selectedPrefix = $rows[0]["selectedPrefix"];
				foreach($rows[0]["prefixes"] as $prefixId){
					$prefix = Loader::getInstance()->prefixManager->getPrefix($prefixId);
					if($prefix !== null){
						$prefixes[] = $prefix;
					}
				}
				if(count($prefixes) === 0){
					$prefixes[] = $this->prefixManager->getDefaultPrefix();
				}
			}
		}else{
			$prefixes[] = $this->prefixManager->getDefaultPrefix();
		}
		$this->sessions[$name] = \WeakReference::create($session = new PrefixSession($name, $player, $customName, $prefixes, $selectedPrefix));
		return $session;
	}

	public function getSession(Player|string $player) : ?PrefixSession{
		return ($this->sessions[strtolower($player instanceof Player ? $player->getName() : $player)] ?? null)?->get();
	}

	public function removeSession(Player|string $player) : void{
		if(isset($this->sessions[strtolower($player instanceof Player ? $player->getName() : $player)])){
			unset($this->sessions[strtolower($player instanceof Player ? $player->getName() : $player)]);
		}
	}

	private function onPlayerChat(PlayerChatEvent $event) : void{
		$player = $event->getPlayer();

		$session = $this->getSession($player);

		if($session === null){
			$event->cancel();
			return;
		}

		$event->setFormatter(new PrefixChatFormatter($player, $session));
	}
}
