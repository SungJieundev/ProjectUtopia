<?php

declare(strict_types=1);

namespace alvin0319\PrefixManager\formatter;

use alvin0319\PrefixManager\session\PrefixSession;
use alvin0319\PrefixManager\util\PrefixUtil;
use pocketmine\lang\Translatable;
use pocketmine\permission\DefaultPermissions;
use pocketmine\player\chat\ChatFormatter;
use pocketmine\player\Player;

final readonly class PrefixChatFormatter implements ChatFormatter{

	public function __construct(
		public Player $player,
		public PrefixSession $session
	){
	}

	public function format(string $username, string $message) : Translatable|string{
		return $this->session->getSelectedPrefix()->prefix . " " . $this->player->getName() . "§r§7 > " . ($this->player->hasPermission(PrefixUtil::OPERATOR_PERMISSION) ? "§a" : "§7") . $message;
	}
}
