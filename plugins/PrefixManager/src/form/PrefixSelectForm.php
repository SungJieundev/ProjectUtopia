<?php

declare(strict_types=1);

namespace alvin0319\PrefixManager\form;

use alvin0319\PrefixManager\Loader;
use alvin0319\PrefixManager\session\PrefixSession;
use pocketmine\form\Form;
use pocketmine\player\Player;
use function array_map;

final class PrefixSelectForm implements Form{

	public function __construct(private PrefixSession $session){ }

	/** @return array<string, mixed> */
	public function jsonSerialize() : array{
		return [
			"type" => "form",
			"title" => "§l칭호 선택",
			"content" => "§l원하시는 칭호를 선택해주세요.",
			"buttons" => array_map(static fn(string $prefix) => ["text" => "- $prefix"], $this->session->getPrefixes())
		];
	}

	public function handleResponse(Player $player, $data) : void{
		if($data === null){
			return;
		}
		$this->session->setSelectedPrefixIndex($data);
		$player->sendMessage(Loader::$prefix . "칭호를 {$this->session->getSelectedPrefix()}§r§7으(로) 선택했습니다.");
	}
}
