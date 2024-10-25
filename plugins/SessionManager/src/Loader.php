<?php

declare(strict_types=1);

namespace alvin0319\SessionManager;

use alvin0319\SessionManager\session\BaseSession;
use pocketmine\event\EventPriority;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\CancelTaskException;
use pocketmine\scheduler\ClosureTask;
use pocketmine\scheduler\TaskHandler;
use pocketmine\utils\SingletonTrait;
use pocketmine\utils\Utils;
use SOFe\AwaitGenerator\Await;
use function spl_object_id;
use function strtolower;

final class Loader extends PluginBase{
	use SingletonTrait;

	/** @var \Closure[] */
	private array $sessionToLoad = [];
	/** @var \Closure[] */
	private array $handlers = [];

	/**
	 * @var BaseSession[][][]
	 * @phpstan-var array<string, BaseSession[]>
	 */
	private array $sessions = [];

	/** @var TaskHandler[] */
	private array $taskHandlers = [];

	protected function onLoad() : void{
		self::setInstance($this);
	}

	protected function onEnable() : void{
		$this->getServer()->getPluginManager()->registerEvent(PlayerLoginEvent::class, function(PlayerLoginEvent $event) : void{
			$event->getPlayer()->setImmobile();
			Await::f2c(function() use ($event) : \Generator{
				$player = $event->getPlayer();
				$name = strtolower($player->getName());
				if(isset($this->sessions[$name])){
					foreach($this->sessions[$name] as $_ => $session){
						if($session->isOffline()){
							$session->setOnline($player);
						}
					}
					return;
				}
				$promises = [];
				foreach($this->sessionToLoad as $index => $registerFunction){
					$promises[$index] = $registerFunction($name, $player, true);
				}
				/** @var BaseSession[] $results */
				// @phpstan-ignore-next-line
				$results = yield from Await::all($promises);
				foreach($results as $i => $session){
					if($session instanceof BaseSession && isset($this->handlers[$i])){
						$this->handlers[$i]($session);
						$this->sessions[$name][spl_object_id($session)] = $session;
					}
				}
				$this->getScheduler()->scheduleRepeatingTask(new ClosureTask(function() use ($player, $results) : void{
					if(!$player->isConnected()){
						throw new CancelTaskException();
					}
					foreach($results as $_ => $session){
						$session->tick();
					}
				}), 20);
				$event->getPlayer()->setImmobile(false);
			});
		}, EventPriority::MONITOR, $this);
		$this->getServer()->getPluginManager()->registerEvent(PlayerQuitEvent::class, function(PlayerQuitEvent $event) : void{
			if(isset($this->sessions[$name = strtolower($event->getPlayer()->getName())])){
				foreach($this->sessions[$name] as $_ => $session){
					$session->onPlayerQuit();
					$session->save();
				}
				unset($this->sessions[$name]);
			}
			if(isset($this->taskHandlers[$name])){
				$this->taskHandlers[$name]->cancel();
				unset($this->taskHandlers[$name]);
			}
		}, EventPriority::NORMAL, $this);
	}

	/**
	 * @phpstan-param \Closure(string) : \Generator<BaseSession> $registerFunction
	 * @phpstan-param \Closure(BaseSession) : void               $handler
	 */
	public function registerSessionLoader(\Closure $registerFunction, \Closure $handler) : void{
		Utils::validateCallableSignature(function(string $name, ?Player $player = null, bool $createIfNotExists = false) : \Generator{ yield; }, $registerFunction);
		Utils::validateCallableSignature(function(BaseSession $session) : void{ }, $handler);
		$registerHandlerId = spl_object_id($registerFunction);
		$this->sessionToLoad[$registerHandlerId] = $registerFunction;
		$this->handlers[$registerHandlerId] = $handler;
	}

	public function onDisable() : void{
		foreach($this->sessions as $sessions){
			foreach($sessions as $_ => $session){
				$session->save();
			}
		}
		$this->sessions = [];
		$this->sessionToLoad = [];
		$this->handlers = [];
		$this->taskHandlers = [];
	}

	public function removeSession(BaseSession $session) : void{
		if(isset($this->sessions[$session->getName()][spl_object_id($session)])){
			unset($this->sessions[$session->getName()][spl_object_id($session)]);
		}
	}

	public static function null(BaseSession $_) : void{}
}
