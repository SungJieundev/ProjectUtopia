<?php

declare(strict_types=1);

namespace alvin0319\EconomyAPI\util;

use pocketmine\utils\EnumTrait;

/**
 * @method static TransactionResult SUCCESS()
 * @method static TransactionResult NOT_LOADED()
 * @method static TransactionResult NOT_ENOUGH_MONEY()
 * @method static TransactionResult MAX_MONEY_EXCEEDED()
 * @method static TransactionResult PLUGIN_CANCELLED()
 * @method static TransactionResult NOT_ALLOWED()
 */
final class TransactionResult{
	use EnumTrait {
		EnumTrait::__construct as EnumTrait___construct;
	}

	protected static function setup() : void{
		self::registerAll(
			new self("success", ""),
			new self("not_loaded", "해당 플레이어는 오프라인입니다."),
			new self("not_enough_money", "돈이 부족합니다."),
			new self("max_money_exceeded", "최대 돈에 도달했습니다."),
			new self("plugin_cancelled", "취소되었습니다."),
			new self("not_allowed", "해당 통화는 거래할 수 없습니다.")
		);
	}

	public function __construct(string $enumName, private string $reason){
		$this->EnumTrait___construct($enumName);
	}

	public function getReason() : string{
		return $this->reason;
	}
}
