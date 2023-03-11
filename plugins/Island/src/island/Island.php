<?php

declare(strict_types=1);

namespace alvin0319\Island\island;

use alvin0319\EconomyAPI\currency\CurrencyWon;
use alvin0319\Island\currency\CurrencyIslandPoint;
use alvin0319\Island\world\IslandDummyChunkLoader;
use pocketmine\math\Vector3;
use pocketmine\permission\DefaultPermissions;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\utils\AssumptionFailedError;
use pocketmine\world\format\Chunk;
use pocketmine\world\Position;
use pocketmine\world\World;
use Symfony\Component\Filesystem\Path;
use function array_filter;
use function array_keys;
use function array_map;
use function count;
use function is_dir;
use function strtolower;
use const INF;

final class Island{

	public const WORLD_ISLAND_PATH = "islands";

	public const CURRENT_MAX_ISLAND_MEMBER = 5;

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
		3 => [10000, CurrencyIslandPoint::class]
	];

	private static Server $server;

	private World $world;

	private IslandDummyChunkLoader $chunkLoader;

	private bool $actionLocked = false;

	/**
	 * @param true[] $members
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
		$world = null;
		if(self::$server->getWorldManager()->getWorldByName($this->worldName) !== null){
			$world = self::$server->getWorldManager()->getWorldByName($this->worldName);
		}elseif(is_dir(Path::join(self::$server->getDataPath(), "worlds", self::WORLD_ISLAND_PATH, $this->worldName))){
			if(self::$server->getWorldManager()->loadWorld(self::WORLD_ISLAND_PATH . "/" . $this->worldName)){
				$world = self::$server->getWorldManager()->getWorldByName($this->worldName);
			}
		}
		if($world === null){
			throw new AssumptionFailedError("Could not find an island world for $this->islandName (owner: $this->owner)");
		}
		$this->world = $world;
		$this->chunkLoader = new IslandDummyChunkLoader();
		$this->world->registerChunkLoader($this->chunkLoader, $this->spawn->getFloorX() >> Chunk::COORD_BIT_SIZE, $this->spawn->getFloorZ() >> Chunk::COORD_BIT_SIZE);
		$this->world->orderChunkPopulation($this->spawn->getFloorX() >> Chunk::COORD_BIT_SIZE, $this->spawn->getFloorZ() >> Chunk::COORD_BIT_SIZE, $this->chunkLoader);
		// pre-setup for spawn
	}

	public function getWorld() : World{
		$world = $this->world;
		if(!$world->isLoaded()){
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

	public function setSpawn(Vector3 $spawn) : void{
		if($this->actionLocked){
			return;
		}
		$oldSpawn = $this->spawn;
		$this->spawn = $spawn;
		if(self::getChunkXZ($oldSpawn) !== self::getChunkXZ($spawn)){
			$this->world->unregisterChunkLoader($this->chunkLoader, $oldSpawn->getFloorX() >> Chunk::COORD_BIT_SIZE, $oldSpawn->getFloorZ() >> Chunk::COORD_BIT_SIZE);
			$this->world->registerChunkLoader($this->chunkLoader, $spawn->getFloorX() >> Chunk::COORD_BIT_SIZE, $spawn->getFloorZ() >> Chunk::COORD_BIT_SIZE);
			$this->world->orderChunkPopulation($spawn->getFloorX() >> Chunk::COORD_BIT_SIZE, $spawn->getFloorZ() >> Chunk::COORD_BIT_SIZE, $this->chunkLoader);
		}
	}

	public function setOwner(string $owner) : void{
		if($this->owner === $owner){
			return;
		}
		$this->owner = $owner;
	}

	public function setIslandName(string $islandName) : void{
		$this->islandName = $islandName;
	}

	public function addIslandLevel(int $level) : void{
		$this->islandLevel += $level;
	}

	public function reduceIslandLevel(int $level) : void{
		$this->islandLevel -= $level;
	}

	public function addMember(Player|string $player) : void{
		$player = strtolower($player instanceof Player ? $player->getName() : $player);
		if(!isset(self::ISLAND_LEVEL_TO_MEMBERS[$this->islandLevel])){
			throw new AssumptionFailedError("Invalid island level $this->islandLevel for $this->islandName (owner: $this->owner)");
		}
		$maxMembers = self::ISLAND_LEVEL_TO_MEMBERS[$this->islandLevel];
		if($maxMembers !== INF && count($this->members) >= $maxMembers){
			return;
		}
		$this->members[$player] = true;
	}

	public function removeMember(Player|string $player, bool $kick = false) : void{
		$player = strtolower($player instanceof Player ? $player->getName() : $player);
		if(!isset($this->members[$player])){
			return;
		}
		unset($this->members[$player]);
		$this->broadcastMessage("§e{$player}§7님이 섬에서 " . ($kick ? "§c추방§7되었" : "탈퇴했") . "습니다.");
	}

	public function isMember(Player|string $player) : bool{
		return isset($this->members[$player = strtolower($player instanceof Player ? $player->getName() : $player)])
			|| ($p = self::$server->getPlayerExact($player)) !== null && $p->hasPermission(DefaultPermissions::ROOT_OPERATOR);
	}

	public function getIslandMaxMembers() : int{
		if(!isset(self::ISLAND_LEVEL_TO_MEMBERS[$this->islandLevel])){
			throw new AssumptionFailedError("Invalid island level $this->islandLevel for $this->islandName (owner: $this->owner)");
		}
		return (int) self::ISLAND_LEVEL_TO_MEMBERS[$this->islandLevel];
	}

	/** @return Player[] */
	public function getOnlineMembers() : array{
		return array_filter(array_map(static fn(string $name) => self::$server->getPlayerExact($name), array_keys($this->members)), static fn(?Player $player) => $player !== null);
	}

	public function broadcastMessage(string $message, ?string $sender = null) : void{
		self::$server->broadcastMessage("§e§l[§f섬 | $this->islandName ] §r§7" . ($sender === null ? "" : " §f$sender §7 > ") . $message, $this->getOnlineMembers());
	}

	/** @phpstan-return array{0: int, 1: int} */
	private static function getChunkXZ(Vector3 $pos) : array{
		return [$pos->getFloorX() >> Chunk::COORD_BIT_SIZE, $pos->getFloorZ() >> Chunk::COORD_BIT_SIZE];
	}
}
