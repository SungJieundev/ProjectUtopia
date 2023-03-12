<?php

declare(strict_types=1);

namespace alvin0319\Warn\session;

use alvin0319\SessionManager\session\BaseSession;
use alvin0319\Warn\Loader;
use pocketmine\player\Player;
use SOFe\AwaitGenerator\Await;
use function array_map;
use function array_sum;
use function array_values;
use function count;
use function date;

final class PlayerWarnSession extends BaseSession{

	public const MAX_WARN = 5;

	/**
	 * @var PlayerWarnData[]
	 * @phpstan-var list<PlayerWarnData>
	 */
	private array $warns = [];

	/** @param array<int, mixed> $warns */
	public function __construct(
		string $name,
		?Player $player = null,
		array $warns = []
	){
		parent::__construct($name, $player);
		if(count($warns) > 0){
			foreach($warns as $warn){
				$this->warns[] = new PlayerWarnData($warn["index"], $warn["name"], $warn["reason"], $warn["time"], $warn["amount"]);
			}
		}
		$this->loaded = true;
	}

	/** @phpstan-return list<PlayerWarnData> */
	public function getWarns() : array{
		return $this->warns;
	}

	public function getWarnCount() : int{
		return array_sum(array_map(static fn(PlayerWarnData $data) => $data->amount, $this->warns));
	}

	public function addWarn(string $reason, int $amount) : void{
		Await::f2c(function() use ($reason, $amount) : \Generator{
			$id = yield from Loader::$database->addWarn(
				$this->name,
				$reason,
				$date = date("Y-m-d H:i:s"),
				$amount
			);
			$this->warns[] = new PlayerWarnData($id, $this->name, $reason, $date, $amount);
			Loader::getInstance()->getServer()->broadcastMessage(Loader::$prefix . "{$this->name}님이 경고 §e{$amount}§7회를 받았습니다. (사유: $reason)\n총 경고 수: " . ($warnCount = $this->getWarnCount()));
			if($warnCount >= self::MAX_WARN){
				Loader::getInstance()->getServer()->broadcastMessage(Loader::$prefix . "{$this->name}님이 경고 초과로 밴 처리 되었습니다.");
				if(($p = $this->getPlayer()) !== null){
					$p->kick("경고 초과로 밴 처리 되었습니다.");
				}
			}
		});
	}

	public function hasWarnIndex(int $index) : bool{
		return isset($this->warns[$index]);
	}

	public function removeWarn(int $index) : void{
		if(!isset($this->warns[$index])){
			return;
		}
		$warnData = $this->warns[$index];
		unset($this->warns[$index]);
		$this->warns = array_values($this->warns);
		Await::f2c(function() use ($warnData) : \Generator{
			yield from Loader::$database->removeWarn($this->name, $warnData->index);
		});
	}

	public function isBanned() : bool{
		return $this->getWarnCount() >= self::MAX_WARN;
	}

	public function save(bool $offline = true) : void{
	}

	public function onPlayerQuit() : void{
		Loader::getInstance()->removeSession($this->name);
	}
}
