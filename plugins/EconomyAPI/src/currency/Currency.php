<?php

declare(strict_types=1);

namespace alvin0319\EconomyAPI\currency;

use pocketmine\player\Player;

interface Currency{

	/**
	 * Returns a name of currency
	 */
	public function getName() : string;

	/**
	 * Returns a symbol of currency
	 */
	public function getSymbol() : string;

	/**
	 * Returns a formatted string of money
	 */
	public function format(int $money, bool $color = false, string $originalColor = "§7") : string;

	/**
	 * Returns a default money
	 */
	public function getDefaultMoney() : int;

	/**
	 * Returns whether the player can transaction or not
	 */
	public function canTransaction(Player|string $player) : bool;
}
