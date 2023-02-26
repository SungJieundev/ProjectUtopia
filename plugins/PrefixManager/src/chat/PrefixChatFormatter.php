<?php

declare(strict_types=1);

namespace alvin0319\PrefixManager\chat;

use alvin0319\PrefixManager\session\PrefixSession;
use pocketmine\lang\Translatable;
use pocketmine\permission\DefaultPermissions;
use pocketmine\player\chat\ChatFormatter;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

final readonly class PrefixChatFormatter implements ChatFormatter{

	public function __construct(private Player $player, private PrefixSession $session){ }

	public function format(string $username, string $message) : Translatable|string{
		$selectedPrefix = $this->session->getSelectedPrefix();
		$nickname = $this->session->getNickname();
		return ("{$selectedPrefix}§r {$nickname}§r §6> §r" . ($this->player->hasPermission(DefaultPermissions::ROOT_OPERATOR) ? "§a" : "") . TextFormat::clean($message));
	}
}
