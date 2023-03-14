<?php

declare(strict_types=1);

namespace alvin0319\Weather;

use alvin0319\Weather\season\Weather;
use alvin0319\Weather\task\BiomeChangeAsyncTask;
use kim\present\lib\arrayutils\ArrayUtils;
use pocketmine\event\entity\EntityTeleportEvent;
use pocketmine\event\EventPriority;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\world\ChunkLoadEvent;
use pocketmine\event\world\WorldLoadEvent;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\timings\TimingsHandler;
use pocketmine\utils\SingletonTrait;
use pocketmine\world\format\Chunk;
use pocketmine\world\format\io\FastChunkSerializer;
use pocketmine\world\format\PalettedBlockArray;
use pocketmine\world\format\SubChunk;
use pocketmine\world\World;

final class Loader extends PluginBase{
	use SingletonTrait;

	/**
	 * @var int[]
	 * @phpstan-param array<int, int>
	 */
	private static array $worldToBiomeId = [];

	public static function setBiomeId(World $world, int $biomeId) : void{
		self::$worldToBiomeId[$world->getId()] = $biomeId;
	}

	public static function removeBiomeId(World $world) : void{
		if(isset(self::$worldToBiomeId[$world->getId()])){
			unset(self::$worldToBiomeId[$world->getId()]);
		}
	}

	public static function getBiomeId(World $world) : ?int{
		return self::$worldToBiomeId[$world->getId()] ?? null;
	}

	public static TimingsHandler $biomeChange;

	protected function onLoad() : void{
		self::setInstance($this);
	}

	protected function onEnable() : void{
		self::$biomeChange = new TimingsHandler("Biome change");
		$this->getServer()->getPluginManager()->registerEvent(EntityTeleportEvent::class, function(EntityTeleportEvent $event) : void{
			$player = $event->getEntity();
			if(!$player instanceof Player){
				return;
			}
			$from = $event->getFrom();
			$to = $event->getTo();
			if($from->getWorld()->getId() === $to->getWorld()->getId()){
				return;
			}
			Weather::clearWeather($player);
		}, EventPriority::NORMAL, $this);
		$this->getServer()->getPluginManager()->registerEvent(PlayerJoinEvent::class, function(PlayerJoinEvent $event) : void{
			$player = $event->getPlayer();
			Weather::SNOWY()->sendWeatherPacket($player);
		}, EventPriority::NORMAL, $this);
		$this->getServer()->getPluginManager()->registerEvent(ChunkLoadEvent::class, function(ChunkLoadEvent $event) : void{
			self::$biomeChange->startTiming();
			$world = $event->getWorld();
			if(isset(self::$worldToBiomeId[$world->getId()])){
				$biomeId = self::$worldToBiomeId[$world->getId()];
				$chunk = $event->getChunk();
				foreach($chunk->getSubChunks() as $subChunkY => $subChunk){
					$newSubChunk = new SubChunk(
						$subChunk->getEmptyBlockId(),
						$subChunk->getBlockLayers(),
						new PalettedBlockArray($biomeId),
						$subChunk->getBlockSkyLightArray(),
						$subChunk->getBlockLightArray()
					);
					$chunk->setSubChunk($subChunkY, $newSubChunk);
				}
				$this->getLogger()->debug("Biome changed for chunk " . $event->getChunkX() . ":" . $event->getChunkZ() . " in world " . $world->getFolderName());
			}
			self::$biomeChange->stopTiming();
		}, EventPriority::NORMAL, $this);
		$this->getServer()->getPluginManager()->registerEvent(WorldLoadEvent::class, function(WorldLoadEvent $event) : void{
			$world = $event->getWorld();
			if(isset(self::$worldToBiomeId[$world->getId()])){
				$this->getServer()->getAsyncPool()->submitTask(new BiomeChangeAsyncTask(
					$world,
					ArrayUtils::mapFromAs($world->getLoadedChunks(), function(Chunk $chunk, string $chunkIndex) : array{
						return [$chunkIndex, FastChunkSerializer::serializeTerrain($chunk)];
					}),
					self::$worldToBiomeId[$world->getId()]
				));
				$this->getLogger()->debug("Biome change task submitted for world " . $world->getDisplayName());
			}
		}, EventPriority::NORMAL, $this);
	}
}
