<?php

declare(strict_types=1);

namespace alvin0319\PrefixManager\form;

use alvin0319\PrefixManager\Loader;
use alvin0319\PrefixManager\session\PrefixSession;
use pocketmine\form\Form;
use pocketmine\item\VanillaItems;
use pocketmine\player\Player;

final class AdminForm implements Form{

	public function __construct(private PrefixSession $session){ }

	/** @return array<string, mixed> */
	public function jsonSerialize() : array{
		return [
			"type" => "form",
			"title" => "§l칭호 관리",
			"content" => "",
			"buttons" => [
				["text" => "§l메인 UI"],
				["text" => "§l자유 칭호권 받기"],
				["text" => "§l닉네임 설정권 받기"]
			]
		];
	}

	public function handleResponse(Player $player, $data) : void{
		if($data === null){
			return;
		}
		switch($data){
			case 0:
				$player->sendForm(new PrefixMainForm($this->session));
				break;
			case 1:
				$item = VanillaItems::PAPER()
					->setCount(1);
				$item->getNamedTag()->setByte(Loader::TAG_FREE_PREFIX_ITEM, 1);
				$player->getInventory()->addItem($item);
				$player->sendMessage(Loader::$prefix . "자유 칭호권을 1개 얻었습니다.");
				break;
			case 2:
				$item = VanillaItems::PAPER()
					->setCount(1);
				$item->getNamedTag()->setByte(Loader::TAG_NICKNAME_ITEM, 1);
				$player->getInventory()->addItem($item);
				$player->sendMessage(Loader::$prefix . "닉네임 설정권을 1개 얻었습니다.");
				break;
		}
	}
}
