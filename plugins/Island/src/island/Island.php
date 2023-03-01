<?php

declare(strict_types=1);

namespace alvin0319\Island\island;

use alvin0319\EconomyAPI\currency\CurrencyWon;
use pocketmine\math\Vector3;
use pocketmine\Server;
use pocketmine\utils\AssumptionFailedError;
use pocketmine\world\Position;
use pocketmine\world\World;
use Symfony\Component\Filesystem\Path;
use function is_dir;

final class Island{

	public const WORLD_ISLAND_PATH = "islands";

	public const ISLAND_LEVEL_TO_MEMBERS = [
		1 => 3,
		2 => 5,
		3 => 7,
		4 => 9,
		5 => INF
	];

	public const COST_TO_UPGRADE = [
		1 => [100000, CurrencyWon::class],
		2 => [300000, CurrencyWon::class],
		3 => [10000]
	];

	private static Server $server;

	private ?World $world = null;

	/**
	 * @param true[]                      $members
	 *
	 * @phpstan-param array<string, true> $members
	 */
	public function __construct(
		public readonly int $id,
		private string $owner,
		private string $worldName,
		private Vector3 $spawn,
		private string $islandName,
		private array $members,
		private int $islandLevel
	){
		if(!isset(self::$server)){
			self::$server = Server::getInstance();
		}
		if(($world = self::$server->getWorldManager()->getWorldByName($this->worldName)) !== null){
			$this->world = $world;
		}elseif(is_dir(Path::join(self::$server->getDataPath(), "worlds", self::WORLD_ISLAND_PATH, $this->worldName))){
			if(self::$server->getWorldManager()->loadWorld(self::WORLD_ISLAND_PATH . "/" . $this->worldName)){
				$this->world = self::$server->getWorldManager()->getWorldByName($this->worldName);
			}
		}
		if($this->world === null){
			throw new AssumptionFailedError("Could not find an island world for $this->islandName (owner: $this->owner)");
		}
	}

	public function getWorld() : World{
		$world = $this->world;
		if($world === null || !$world->isLoaded()){
			throw new AssumptionFailedError("Assumed world is loaded for $this->islandName (owner: $this->owner)");
		}
		return $world;
	}

	public function getOwner() : string{
		return $this->owner;
	}

	public function getWorldName() : string{
		return $this->worldName;
	}

	public function getSpawn() : Vector3{
		return $this->spawn;
	}

	public function getIslandName() : string{
		return $this->islandName;
	}

	/**
	 * @return true[]
	 *
	 * @phpstan-return array<string, true>
	 */
	public function getMembers() : array{
		return $this->members;
	}

	public function getIslandLevel() : int{
		return $this->islandLevel;
	}

	public function getSpawnPos() : Position{
		return Position::fromObject($this->spawn, $this->world);
	}
}