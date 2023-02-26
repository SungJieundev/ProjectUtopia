<?php

declare(strict_types=1);

namespace alvin0319\PrefixManager\form;

use alvin0319\PrefixManager\session\PrefixSession;
use pocketmine\form\Form;
use pocketmine\player\Player;
use function is_int;

final class PrefixMainForm implements Form{

	public function __construct(private PrefixSession $session){ }

	/** @return array<string, mixed> */
	public function jsonSerialize() : array{
		return [
			"type" => "form",
			"title" => "§l칭호",
			"content" => "",
			"buttons" => [
				["text" => "§l칭호 선택\n§r§d* §8칭호를 선택합니다."],
				["text" => "§l자유칭호 만들기\n§r§d* §8자유칭호를 만듭니다."],
				["text" => "§l닉네임 바꾸기\n§r§d* §8닉네임을 바꿉니다."]
			]
		];
	}

	public function handleResponse(Player $player, $data) : void{
		if(!is_int($data)){
			return;
		}
		switch($data){
			case 0:
				$player->sendForm(new PrefixSelectForm($this->session));
				break;
			case 1:
				$player->sendForm(new MakePrefixForm($this->session));
				break;
			case 2:
				$player->sendForm(new SetNicknameForm($this->session));
				break;
		}
	}
}
