<?php

declare(strict_types=1);

namespace alvin0319\EconomyAPI\currency;

use pocketmine\player\Player;
use function count;
use function floor;

final class CurrencyWon implements Currency{

	public const BIG_ORDER = ['', '만 ', '억 ', '조 ', '경 '];

	public function getName() : string{
		return "won";
	}

	public function getSymbol() : string{
		return "원";
	}

	public function format(int $money, bool $color = false, string $originalColor = "§7") : string{
		$str = '';
		for($i = count(self::BIG_ORDER) - 1; $i >= 0; --$i){
			$unit = 10000 ** $i;
			$part = floor($money / $unit);
			if($part > 0){
				$str .= $color ? "§a" . $part . $originalColor . self::BIG_ORDER[$i] : $part . self::BIG_ORDER[$i];
			}
			$money %= $unit;
		}
		if($str === ""){
			$str = "0";
		}
		return $str . $this->getSymbol();
	}

	public function getDefaultMoney() : int{
		return 30000;
	}

	public function canTransaction(Player|string $player) : bool{
		return true;
	}
}
