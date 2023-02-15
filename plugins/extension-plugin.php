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

use pocketmine\plugin\PluginBase;
use pocketmine\utils\AssumptionFailedError;
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

final class ExtensionPlugin extends PluginBase{
}