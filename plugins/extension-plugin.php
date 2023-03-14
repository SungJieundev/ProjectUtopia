<?php

/**
 * @name extension-plugin
 * @author alvin0319
 * @main alvin0319\ExtensionPlugin\ExtensionPlugin
 * @version 1.0.0
 * @api 5.0.0
 */

declare(strict_types=1);

namespace alvin0319\ExtensionPlugin;

use pocketmine\event\EventPriority;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\item\Item;
use pocketmine\nbt\LittleEndianNbtSerializer;
use pocketmine\nbt\TreeRoot;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\AssumptionFailedError;
use Symfony\Component\Filesystem\Path;
use function array_map;
use function array_pop;
use function base64_encode;
use function bin2hex;
use function count;
use function hexdec;
use function imagealphablending;
use function imagecolorallocatealpha;
use function imagecolorat;
use function imagecolorsforindex;
use function imagecopyresized;
use function imagecreatefrompng;
use function imagecreatetruecolor;
use function imagedestroy;
use function imagefill;
use function imagepng;
use function imagesavealpha;
use function imagesetpixel;
use function imagesx;
use function imagesy;
use function is_string;
use function mkdir;
use function str_split;
use function unlink;

/**
 * @template T of mixed
 * @phpstan-param T|null $value
 * @phpstan-return T
 */
function assumeNotNull(mixed $value, \Closure|string $message = "This should never be null") : mixed{
	if($value === null){
		throw new AssumptionFailedError(is_string($message) ? $message : $message());
	}
	return $value;
}

$serializer = new LittleEndianNbtSerializer();

function encodeItem(Item $item) : string{
	global $serializer;
	return base64_encode($serializer->write(new TreeRoot($item->nbtSerialize())));
}

function decodeItem(string $data) : Item{
	global $serializer;
	return Item::nbtDeserialize($serializer->read(base64_encode($data))->mustGetCompoundTag());
}

function makePHPStanHappy() : void{
}

final class ExtensionPlugin extends PluginBase{

	protected function onEnable() : void{
//		@mkdir(Path::join($this->getDataFolder(), 'heads'));
//		@mkdir(Path::join($this->getDataFolder(), "skins"));
//		$this->getServer()->getPluginManager()->registerEvent(PlayerJoinEvent::class, function(PlayerJoinEvent $event) : void{
//			$this->savePlayerHead($event->getPlayer());
//		}, EventPriority::NORMAL, $this);
	}

	public function savePlayerSkin(Player $player, int $height = 64, int $width = 64) : void{
		$pixel_array = str_split(bin2hex($player->getSkin()->getSkinData()), 8);
		$image = imagecreatetruecolor($width, $height);
		if($image === false){
			throw new AssumptionFailedError("Failed to create GdImage instance");
		}
		imagealphablending($image, false);
		imagesavealpha($image, true);
		$position = count($pixel_array) - 1;
		while(!empty($pixel_array)){
			$x = $position % $width;
			$y = ($position - $x) / $height;
			$walkable = str_split(array_pop($pixel_array), 2);
			$color = array_map(static function(string $val){
				return (int) hexdec($val);
			}, $walkable);
			$alpha = array_pop($color);
			$alpha = ((~((int) $alpha)) & 0xff) >> 1;
			$color[] = $alpha;
			$allocatedImage = imagecolorallocatealpha($image, ...$color);
			if($allocatedImage === false){
				throw new AssumptionFailedError("Failed to allocate color");
			}
			imagesetpixel($image, $x, $y, $allocatedImage);
			$position--;
		}
		@unlink(Path::join($this->getDataFolder(), 'skins', $player->getName() . '.png'));
		imagepng($image, Path::join($this->getDataFolder(), 'skins', $player->getName() . '.png'));
		@imagedestroy($image);
	}

	public function savePlayerHead(Player $player) : void{
		$this->savePlayerSkin($player);
		$path = Path::join($this->getDataFolder(), 'skins', $player->getName() . '.png');
		$image = imagecreatefrompng($path);
		if($image === false){
			throw new AssumptionFailedError("Failed to create GdImage instance");
		}
		$objective = $this->createHeadImage(8, 8);
		for($y = 8; $y < 16; ++$y){
			for($x = 8; $x < 16; ++$x){
				$coordinate = imagecolorat($image, $x, $y);
				if($coordinate === false){
					throw new AssumptionFailedError("Failed to get color at $x, $y");
				}
				imagesetpixel($objective, $x - 8, $y - 8, $coordinate);
			}
		}
		for($y = 7; $y < 15; ++$y){
			for($x = 40; $x < 48; ++$x){
				$color = imagecolorat($image, $x, $y);
				if($color === false){
					throw new AssumptionFailedError("Failed to get color at $x, $y");
				}
				$index = imagecolorsforindex($image, $color);
				if($index["alpha"] === 127){
					continue;
				}
				imagesetpixel($objective, $x - 40, $y - 8, $color);
			}
		}
		imagedestroy($image);
		$final = $this->createHeadImage(330, 360);
		imagecopyresized($final, $objective, 0, 0, 0, 0, imagesx($final), imagesy($final), imagesx($objective), imagesy($objective));
		imagedestroy($objective);
		@unlink(Path::join($this->getDataFolder(), 'heads', $player->getName() . '.png'));
		imagepng($final, Path::join($this->getDataFolder(), 'heads', $player->getName() . '.png'));
		imagedestroy($final);
	}

	private function createHeadImage(int $width, int $height) : \GdImage{
		$image = imagecreatetruecolor($width, $height);
		if($image === false){
			throw new AssumptionFailedError("Failed to create GdImage instance");
		}
		imagesavealpha($image, true);
		imagealphablending($image, false);
		$fill = imagecolorallocatealpha($image, 255, 255, 255, 127);
		if($fill === false){
			throw new AssumptionFailedError("Failed to allocate color");
		}
		imagefill($image, 0, 0, $fill);
		return $image;
	}
}
