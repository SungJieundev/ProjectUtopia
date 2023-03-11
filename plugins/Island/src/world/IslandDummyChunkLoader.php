<?php

declare(strict_types=1);

namespace alvin0319\Island\world;

use pocketmine\world\ChunkLoader;

/**
 * A dummy class that used to prevent chunks from being unloaded.
 * Caution using this class, this class might have memory leak.
 */
final class IslandDummyChunkLoader implements ChunkLoader{
}
