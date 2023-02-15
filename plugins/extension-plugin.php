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

use pocketmine\item\Item;
use pocketmine\nbt\LittleEndianNbtSerializer;
use pocketmine\nbt\TreeRoot;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\AssumptionFailedError;
use function base64_encode;
use function is_string;

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

final class ExtensionPlugin extends PluginBase{
}
