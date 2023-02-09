<?php

declare(strict_types=1);

namespace alvin0319\EconomyAPI\event;

use alvin0319\EconomyAPI\util\Transaction;
use pocketmine\event\Cancellable;
use pocketmine\event\CancellableTrait;
use pocketmine\event\Event;

final class TransactionEvent extends Event implements Cancellable{
	use CancellableTrait;

	public function __construct(private readonly Transaction $transaction){ }

	public function getTransaction() : Transaction{
		return $this->transaction;
	}
}