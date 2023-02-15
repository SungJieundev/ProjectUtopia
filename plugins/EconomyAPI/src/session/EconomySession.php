<?php

declare(strict_types=1);

namespace alvin0319\EconomyAPI\session;

use alvin0319\EconomyAPI\currency\Currency;
use alvin0319\EconomyAPI\EconomyAPI;
use alvin0319\SessionManager\session\BaseSession;
use pocketmine\player\Player;
use pocketmine\utils\Utils;
use SOFe\AwaitGenerator\Await;
use function count;

final class EconomySession extends BaseSession{

	/** @var int[] */
	private array $currencies = [];

	/** @var \Closure[] */
	private array $queuedClosures = [];

	private int $offlineTick = 0;

	/**
	 * @param string      $name
	 * @param EconomyAPI  $plugin
	 * @param Player|null $player
	 * @param int[]       $currencies
	 */
	public function __construct(string $name, public readonly EconomyAPI $plugin, ?Player $player = null, array $currencies = []){
		parent::__construct($name, $player);
		if(count($currencies) > 0){
			$this->currencies = $currencies;
			$this->loaded = true;
		}else{
			Await::f2c(function() : \Generator{
				foreach($this->plugin->getCurrencies() as $_ => $currency){
					$ref = 0;
					for($i = 0; $i < 3; $i++){
						$rows = yield from EconomyAPI::$database->economyapiGet($this->name, $currency->getName());
						if(count($rows) > 0){
							if($rows[0]["transactionBlocked"]){
								if(++$ref < 3){
									$this->plugin->getLogger()->debug("The Database reported that the user $this->name is loaded on another server, waiting for the server to update. (retry: $ref)");
									yield from EconomyAPI::$std->sleep(20); // wait 1 second
									continue;
								}
								$this->plugin->getLogger()->debug("Tried to wait for 3 seconds but the Database reported that the user is not updated. Forcing to load data.");
							}
							$this->onCurrencyInitialized($currency, (int) $rows[0]["money"]);
						}else{
							yield from EconomyAPI::$database->economyapiCreate($this->name, $currency->getName(), $currency->getDefaultMoney());
							$this->onCurrencyInitialized($currency, $currency->getDefaultMoney());
						}
						break;
					}
				}
				$this->loaded = true;
			});
		}
	}

	public function getName() : string{
		return $this->name;
	}

	private function onCurrencyInitialized(Currency $currency, int $money) : void{
		$this->currencies[$currency->getName()] = $money;
		if(count($this->currencies) === count($this->plugin->getCurrencies())){
			$this->loaded = true;
			$this->plugin->getLogger()->debug("User $this->name has been loaded.");
			foreach($this->queuedClosures as $closure){
				$closure();
			}
			$this->queuedClosures = [];
		}
	}

	public function isOffline() : bool{
		return $this->player === null || !$this->player->isConnected();
	}

	public function getPlayer() : ?Player{
		return $this->player;
	}

	public function setOnline(Player $player) : void{
		$this->player = $player;
	}

	public function addMoney(int $money, Currency $currency, bool $sync = true) : void{
		$this->currencies[$currency->getName()] += $money;
		if($sync){
			Await::g2c(EconomyAPI::$database->economyapiUpdate($this->name, $currency->getName(), $this->currencies[$currency->getName()], 1));
			$this->notifyUpdate($currency);
		}
	}

	public function reduceMoney(int $money, Currency $currency, bool $sync = true) : void{
		$this->currencies[$currency->getName()] -= $money;
		if($sync){
			Await::g2c(EconomyAPI::$database->economyapiUpdate($this->name, $currency->getName(), $this->currencies[$currency->getName()], 1));
			$this->notifyUpdate($currency);
		}
	}

	public function setMoney(int $money, Currency $currency, bool $sync = true) : void{
		$this->currencies[$currency->getName()] = $money;
		if($sync){
			Await::g2c(EconomyAPI::$database->economyapiUpdate($this->name, $currency->getName(), $this->currencies[$currency->getName()], 1));
			$this->notifyUpdate($currency);
		}
	}

	public function getMoney(?Currency $currency = null) : int{
		$currency ??= $this->plugin->getDefaultCurrency();
		return $this->currencies[$currency->getName()] ?? 0;
	}

	public function save(bool $offline = true) : void{
		foreach($this->currencies as $name => $money){
			Await::g2c(EconomyAPI::$database->economyapiUpdate($this->name, $name, $money, $offline ? 0 : 1));
		}
	}

	/**
	 * Queues pending closure and execute when they're loaded or immediately if they're already loaded.
	 * Note that queueing ANY transaction in the closure is not guaranteed.
	 *
	 * @phpstan-param \Closure() : void $closure
	 */
	public function queueClosure(\Closure $closure) : void{
		Utils::validateCallableSignature(function(){ }, $closure);
		if($this->loaded){
			$closure();
			return;
		}
		$this->queuedClosures[] = $closure;
	}

	public function notifyUpdate(Currency $currency) : void{
		// TODO
	}

	public function onUpdateNotify(Currency $currency) : void{
		// TODO
	}

	public function tick() : void{
		if($this->player === null){
			if(++$this->offlineTick >= 60){
				$this->forceRemoveSession();
			}
		}
	}

	public function onPlayerQuit() : void{
		$this->plugin->removeSession($this->name);
	}
}