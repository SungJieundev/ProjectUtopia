<?php

declare(strict_types=1);

namespace alvin0319\PrefixManager\form;

use alvin0319\PrefixManager\session\PrefixSession;
use alvin0319\PrefixManager\util\PrefixUtil;
use pocketmine\form\Form;
use pocketmine\player\Player;
use function is_int;

final readonly class PrefixMainForm implements Form{

	public function __construct(public PrefixSession $session, public Player $player){ }

	/** @phpstan-return SimpleForm */
	public function jsonSerialize() : array{
		$buttons = [
			["text" => "§l칭호 목록\n§r§8현재 소유중인 칭호 목록을 확인, 또는 착용합니다."],
			["text" => "§l자유칭호 사용하기\n§r§8자유칭호권을 소모해 칭호를 제작합니다."],
			["text" => "§l닉네임 변경하기\n§r§8닉네임권을 소모해 닉네임을 설정합니다."]
		];
		if($this->player->hasPermission(PrefixUtil::OPERATOR_PERMISSION)){
			$buttons[] = ["text" => "§l칭호 추가하기\n§r§8칭호를 추가합니다."];
			$buttons[] = ["text" => "§l닉네임 설정하기\n§r§8닉네임을 설정합니다."];
		}
		return [
			"type" => "form",
			"title" => "§l칭호",
			"content" => "",
			"buttons" => $buttons
		];
	}

	public function handleResponse(Player $player, $data) : void{
		if(!is_int($data)){
			return;
		}
		$form = match ($data){
			0 => new PrefixListForm($this->session, $player),
			default => null
		};
		if($form === null){
			return;
		}
		$player->sendForm($form);
	}
}
