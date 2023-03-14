<?php

declare(strict_types=1);

namespace alvin0319\Weather\season;

use alvin0319\Weather\Loader;
use alvin0319\Weather\task\BiomeChangeAsyncTask;
use pocketmine\data\bedrock\BiomeIds;
use pocketmine\network\mcpe\protocol\LevelEventPacket;
use pocketmine\network\mcpe\protocol\types\LevelEvent;
use pocketmine\player\Player;
use pocketmine\timings\TimingsHandler;
use pocketmine\utils\EnumTrait;
use pocketmine\world\format\io\FastChunkSerializer;
use pocketmine\world\format\PalettedBlockArray;
use pocketmine\world\format\SubChunk;
use pocketmine\world\World;

/**
 * @method static Weather SUNNY()
 * @method static Weather RAINY()
 * @method static Weather SNOWY()
 * @method static Weather THUNDER()
 */
final class Weather{
	use EnumTrait {
		__construct as EnumTrait__Construct;
	}

	public const START = "start";
	public const STOP = "stop";

	public static TimingsHandler $weatherChange;

	protected static function setup() : void{
		self::registerAll(
			new self("sunny", [
				LevelEvent::STOP_RAIN,
				LevelEvent::STOP_THUNDER
			], BiomeIds::PLAINS, true),
			new self("rainy", [
				self::START => LevelEvent::START_RAIN,
				self::STOP => LevelEvent::STOP_RAIN
			], BiomeIds::PLAINS, false),
			new self("snowy", [
				self::START => LevelEvent::START_RAIN,
				self::STOP => LevelEvent::STOP_RAIN
			], BiomeIds::ICE_PLAINS, false),
			new self("thunder", [
				self::START => LevelEvent::START_THUNDER,
				self::STOP => LevelEvent::STOP_THUNDER
			], BiomeIds::PLAINS, false)
		);
	}

	/** @phpstan-param list<int>|array<string, int> $eventTypes */
	public function __construct(
		string $enumName,
		public readonly array $eventTypes,
		public readonly int $biomeId,
		public readonly bool $stopOnly
	){
		if(!isset(self::$weatherChange)){
			self::$weatherChange = new TimingsHandler("Weather Change", null);
		}
		$this->EnumTrait__Construct($enumName);
	}

	public function sendWeatherPacket(Player $player, bool $stop = false) : void{
		self::$weatherChange->startTiming();
		$world = $player->getWorld();
		$chunks = [];
		Loader::setBiomeId($world, $this->biomeId);
		foreach($world->getLoadedChunks() as $chunkIndex => $chunk){
			$chunks[$chunkIndex] = FastChunkSerializer::serializeTerrain($chunk);
		}
		$player->getServer()->getAsyncPool()->submitTask(new BiomeChangeAsyncTask($world, $chunks, $this->biomeId));
		if($stop){
			if($this->stopOnly){
				foreach($this->eventTypes as $eventType){
					$player->getNetworkSession()->sendDataPacket(
						LevelEventPacket::create(
							$eventType,
							0,
							$player->getPosition()->asVector3()
						)
					);
				}
			}else{
				$player->getNetworkSession()->sendDataPacket(
					LevelEventPacket::create(
						$this->eventTypes[self::STOP],
						0,
						$player->getPosition()->asVector3()
					)
				);
			}
		}else{
			if(!$this->stopOnly){
				$player->getNetworkSession()->sendDataPacket(
					LevelEventPacket::create(
						$this->eventTypes[self::START],
						10000,
						$player->getPosition()->asVector3()
					)
				);
			}
		}
		self::$weatherChange->stopTiming();
	}

	public static function clearWeather(Player $player) : void{
		$player->getNetworkSession()->sendDataPacket(
			LevelEventPacket::create(
				LevelEvent::STOP_RAIN,
				0,
				$player->getPosition()->asVector3()
			)
		);
		$player->getNetworkSession()->sendDataPacket(
			LevelEventPacket::create(
				LevelEvent::STOP_THUNDER,
				0,
				$player->getPosition()->asVector3()
			)
		);
	}
}
