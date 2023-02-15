<?php

declare(strict_types=1);

namespace alvin0319\EconomyAPI\util;

use alvin0319\EconomyAPI\currency\Currency;
use alvin0319\EconomyAPI\EconomyAPI;
use alvin0319\EconomyAPI\event\TransactionEvent;
use alvin0319\EconomyAPI\session\EconomySession;
use pocketmine\player\Player;
use pocketmine\utils\AssumptionFailedError;
use const PHP_INT_MAX;

final class Transaction{

	private EconomySession $session;

	private ?EconomySession $receiver = null;

	private Currency $currency;

	public function __construct(Player|string $sender, private int $amount, private TransactionType $transactionType, ?Currency $currency = null, Player|string|null $receiver = null){
		$this->currency = $currency ?? EconomyAPI::getInstance()->getDefaultCurrency();
		$senderSession = EconomyAPI::getInstance()->getSession($sender);
		if($senderSession === null){
			throw new \InvalidArgumentException("Tried to create transaction with an invalid player");
		}
		$this->session = $senderSession;
		if($this->transactionType->equals(TransactionType::PAY())){
			if($receiver === null){
				throw new AssumptionFailedError("Tried to create transaction with PAY type but receiver is null");
			}
			$receiverSession = EconomyAPI::getInstance()->getSession($receiver);
			if($receiverSession === null){
				throw new \InvalidArgumentException("Tried to create transaction with an invalid player");
			}
			$this->receiver = $receiverSession;
		}
	}

	public function getSession() : EconomySession{
		return $this->session;
	}

	public function getReceiverSession() : ?EconomySession{
		return $this->receiver;
	}

	public function getAmount() : int{
		return $this->amount;
	}

	public function getCurrency() : Currency{
		return $this->currency;
	}

	public function getTransactionType() : TransactionType{
		return $this->transactionType;
	}

	public function execute() : TransactionResult{
		if(!$this->session->isLoaded()){
			return TransactionResult::NOT_LOADED();
		}
		$ev = new TransactionEvent($this);
		$ev->call();
		if($ev->isCancelled()){
			return TransactionResult::PLUGIN_CANCELLED();
		}
		switch(true){
			case $this->transactionType->equals(TransactionType::ADD()):
				if($this->session->getMoney($this->currency) + $this->amount >= PHP_INT_MAX){
					return TransactionResult::MAX_MONEY_EXCEEDED();
				}
				$this->session->addMoney($this->amount, $this->currency);
				return TransactionResult::SUCCESS();
			case $this->transactionType->equals(TransactionType::REDUCE()):
				if($this->session->getMoney($this->currency) - $this->amount < 0){
					return TransactionResult::NOT_ENOUGH_MONEY();
				}
				$this->session->reduceMoney($this->amount, $this->currency);
				return TransactionResult::SUCCESS();
			case $this->transactionType->equals(TransactionType::PAY()):
				$receiver = $this->receiver;
				if($receiver === null){
					throw new AssumptionFailedError("Tried to execute transaction with PAY type but receiver is null");
				}
				if(!$receiver->isLoaded()){
					return TransactionResult::NOT_LOADED();
				}
				if(!$this->currency->canTransaction($this->session->getName()) || !$this->currency->canTransaction($receiver->getName())){
					return TransactionResult::NOT_ALLOWED();
				}
				if($this->session->getMoney($this->currency) - $this->amount < 0){
					return TransactionResult::NOT_ENOUGH_MONEY();
				}
				if($receiver->getMoney($this->currency) + $this->amount >= PHP_INT_MAX){
					return TransactionResult::MAX_MONEY_EXCEEDED();
				}
				$this->session->reduceMoney($this->amount, $this->currency);
				$receiver->addMoney($this->amount, $this->currency);
				return TransactionResult::SUCCESS();
			case $this->transactionType->equals(TransactionType::SET()):
				if($this->amount > PHP_INT_MAX){
					return TransactionResult::MAX_MONEY_EXCEEDED();
				}
				if($this->amount < 0){
					return TransactionResult::NOT_ENOUGH_MONEY();
				}
				$this->session->setMoney($this->amount, $this->currency);
				return TransactionResult::SUCCESS();
			default:
				throw new AssumptionFailedError("Tried to execute transaction with an invalid transaction type");
		}
	}
}
