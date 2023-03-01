<?php

declare(strict_types=1);

namespace alvin0319\Island\currency;

use alvin0319\EconomyAPI\currency\Currency;
use pocketmine\player\Player;

final class CurrencyIslandPoint implements Currency{
	public function getName() : string{
		return "섬 포인트";
	}

	public function getSymbol() : string{
		return "§bI§fP§r";
	}

	public function format(int $money, bool $color = false, string $originalColor = "§7") : string{
		return $money . $this->getSymbol() . $originalColor;
	}

	public function getDefaultMoney() : int{
		return 0;
	}

	public function canTransaction(Player|string $player) : bool{
		return false;
	}
}