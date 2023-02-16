<?php

declare(strict_types=1);

namespace alvin0319\EconomyAPI;

use alvin0319\EconomyAPI\command\AddMoneyCommand;
use alvin0319\EconomyAPI\command\MyMoneyCommand;
use alvin0319\EconomyAPI\command\PayCommand;
use alvin0319\EconomyAPI\command\SetMoneyCommand;
use alvin0319\EconomyAPI\command\TakeMoneyCommand;
use alvin0319\EconomyAPI\command\TopMoneyCommand;
use alvin0319\EconomyAPI\currency\Currency;
use alvin0319\EconomyAPI\currency\CurrencyWon;
use alvin0319\EconomyAPI\session\EconomySession;
use alvin0319\SessionManager\Loader;
use alvin0319\SessionManager\session\BaseSession;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\SingletonTrait;
use poggit\libasynql\DataConnector;
use poggit\libasynql\libasynql;
use SOFe\AwaitGenerator\Await;
use SOFe\AwaitStd\AwaitStd;
use function count;
use function strtolower;

final class EconomyAPI extends PluginBase{
	use SingletonTrait;

	public static string $prefix = "§b§l[알림] §r§7";

	private DataConnector $connector;

	public static Database $database;

	public static AwaitStd $std;

	private Currency $defaultCurrency;

	/** @var Currency[] */
	private array $currencies = [];
	/** @var EconomySession[] */
	private array $sessions = [];

	protected function onLoad() : void{
		self::setInstance($this);
	}

	protected function onEnable() : void{
		$this->connector = libasynql::create($this, $this->getConfig()->get("database"), [
			"sqlite" => "sqlite.sql",
			"mysql" => "mysql.sql"
		]);
		self::$database = new Database($this->connector);
		self::$std = AwaitStd::init($this);
		Await::g2c(self::$database->economyapiInit());
		$this->connector->waitAll();
		$this->initializeCurrencies();
		$this->registerCommands();
		Loader::getInstance()->registerSessionLoader($this->createSession(...), function(BaseSession $session) : void{
			$session->getPlayer()?->sendMessage("Session loaded!");
		});
	}

	protected function onDisable() : void{
		$this->sessions = [];
		$this->connector->waitAll();
		$this->connector->close();
	}

	private function initializeCurrencies() : void{
		$this->registerCurrency($this->defaultCurrency = new CurrencyWon());
	}

	private function registerCommands() : void{
		$this->getServer()->getCommandMap()->registerAll("economyapi", [
			new AddMoneyCommand(),
			new MyMoneyCommand(),
			new PayCommand(),
			new SetMoneyCommand(),
			new TakeMoneyCommand(),
			new TopMoneyCommand()
		]);
	}

	public function registerCurrency(Currency $currency) : void{
		if(isset($this->currencies[$currency->getName()])){
			throw new \InvalidArgumentException("Cannot register currency with duplicated name");
		}
		$this->currencies[$currency->getName()] = $currency;
	}

	public function getCurrency(string $name) : ?Currency{
		return $this->currencies[$name] ?? null;
	}

	/** @phpstan-return array<string, Currency> */
	public function getCurrencies() : array{
		return $this->currencies;
	}

	public function getDefaultCurrency() : Currency{
		return $this->defaultCurrency;
	}

	public function setDefaultCurrency(Currency $currency) : void{
		$this->defaultCurrency = $currency;
	}

	public function createSession(string $name, ?Player $player = null, bool $createIfNotExists = false) : \Generator{
		$currencies = [];
		$exists = true;
		foreach($this->currencies as $_ => $currency){
			$rows = yield from self::$database->economyapiGet($name, $currency->getName());
			if(count($rows) > 0){
				if(!$rows[0]["transactionBlocked"]){
					$currencies[$currency->getName()] = (int) $rows[0]["money"];
				}
			}else{
				$exists = false;
			}
		}
		if(!$exists && !$createIfNotExists){
			return null;
		}
		return $this->sessions[$name] = new EconomySession($name, $this, $player, $currencies);
	}

	public function getSession(Player|string $player) : ?EconomySession{
		return $this->sessions[strtolower($player instanceof Player ? $player->getName() : $player)] ?? null;
	}

	public function removeSession(Player|string $player) : void{
		if(isset($this->sessions[strtolower($player instanceof Player ? $player->getName() : $player)])){
			unset($this->sessions[strtolower($player instanceof Player ? $player->getName() : $player)]);
		}
	}
}
