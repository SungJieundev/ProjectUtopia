<?php

declare(strict_types=1);

namespace alvin0319\EconomyAPI\util;

use pocketmine\utils\EnumTrait;

/**
 * @method static TransactionType ADD()
 * @method static TransactionType REDUCE()
 * @method static TransactionType SET()
 * @method static TransactionType PAY()
 */
final class TransactionType{
	use EnumTrait;

	protected static function setup() : void{
		self::registerAll(
			new self("add"),
			new self("reduce"),
			new self("set"),
			new self("pay")
		);
	}
}