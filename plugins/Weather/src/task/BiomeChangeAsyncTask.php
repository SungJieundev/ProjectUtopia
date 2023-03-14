<?php

declare(strict_types=1);

namespace alvin0319\Weather\task;

use pocketmine\scheduler\AsyncTask;
use pocketmine\world\format\io\FastChunkSerializer;
use pocketmine\world\format\PalettedBlockArray;
use pocketmine\world\format\SubChunk;
use pocketmine\world\World;
use function igbinary_serialize;
use function igbinary_unserialize;
use function microtime;

final class BiomeChangeAsyncTask extends AsyncTask{

	public const TAG_WORLD = "world";

	public readonly \ThreadedArray $chunks;

	/** @param string[] $chunks */

	private float $startTime;

	/** @param string[] $chunks */
	public function __construct(World $world, array $chunks, private readonly int $biomeId){
		$this->storeLocal(self::TAG_WORLD, $world);
		$this->chunks = \ThreadedArray::fromArray($chunks);
		$this->startTime = microtime(true);
	}

	public function onRun() : void{
		$data = (array) $this->chunks;
		$chunks = [];
		$biomeArray = new PalettedBlockArray($this->biomeId);
		foreach($data as $chunkIndex => $chunkData){
			$chunk = FastChunkSerializer::deserializeTerrain($chunkData);
			foreach($chunk->getSubChunks() as $subChunkY => $subChunk){
				$newSubChunk = new SubChunk(
					$subChunk->getEmptyBlockId(),
					$subChunk->getBlockLayers(),
					$biomeArray,
					$subChunk->getBlockSkyLightArray(),
					$subChunk->getBlockLightArray()
				);
				$chunk->setSubChunk($subChunkY, $newSubChunk);
			}
			$chunks[$chunkIndex] = FastChunkSerializer::serializeTerrain($chunk);
		}
		$this->setResult(igbinary_serialize($chunks));
	}

	public function onCompletion() : void{
		$endTime = microtime(true);
		/** @var World $world */
		$world = $this->fetchLocal(self::TAG_WORLD);
		/** @var string[] $chunks */
		$chunks = (array) igbinary_unserialize($this->getResult());
		foreach($chunks as $chunkIndex => $chunkData){
			$chunk = FastChunkSerializer::deserializeTerrain($chunkData);
			World::getXZ($chunkIndex, $chunkX, $chunkZ);
			$world->setChunk($chunkX, $chunkZ, $chunk);
		}
		$finalizedTime = microtime(true);
		$world->getLogger()->debug("BiomeChangeAsyncTask completed in " . ($endTime - $this->startTime) . "s (finalized in " . ($finalizedTime - $endTime) . "s)");
	}
}
