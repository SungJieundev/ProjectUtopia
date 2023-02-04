<?php

declare(strict_types=1);

namespace alvin0319\EconomyAPI\currency;

use pocketmine\player\Player;

interface Currency{

	public function getName() : string;

	public function getSymbol() : string;

	public function format(int $money, bool $color = false, string $originalColor = "§7") : string;

	public function getDefaultMoney() : int;

	public function canTransaction(Player|string $player) : bool;
}