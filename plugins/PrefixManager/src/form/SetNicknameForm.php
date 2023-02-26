<?php

declare(strict_types=1);

namespace alvin0319\PrefixManager\form;

use alvin0319\PrefixManager\Loader;
use alvin0319\PrefixManager\session\PrefixSession;
use pocketmine\form\Form;
use pocketmine\item\VanillaItems;
use pocketmine\player\Player;
use function count;
use function is_array;
use function preg_match_all;
use function str_contains;

final class SetNicknameForm implements Form{

	public function __construct(private PrefixSession $session){ }

	/** @return array<string, mixed> */
	public function jsonSerialize() : array{
		return [
			"type" => "custom_form",
			"title" => "§l닉네임 설정하기",
			"content" => [
				[
					"type" => "label",
					"text" => "§l§c※ 주의사항§r§f\n- 닉네임은 최대 §a5글자§8까지 입력할 수 있습니다.\n- 설정하면 되돌릴 수 없습니다.\n- 닉네임에는 한 가지 색만 사용할 수 있습니다. (굵기 X)"
				],
				[
					"type" => "input",
					"text" => "§d* §8닉네임 입력 (위의 주의사항을 꼭 읽어주세요.)"
				]
			]
		];
	}

	public function handleResponse(Player $player, $data) : void{
		if(!is_array($data) || count($data) !== 2){
			return;
		}
		[, $nickname] = $data;
		$item = VanillaItems::PAPER()
			->setCount(1);
		$item->getNamedTag()->setByte(Loader::TAG_NICKNAME_ITEM, 1);
		if(!$player->getInventory()->contains($item)){
			$player->sendMessage(Loader::$prefix . "닉네임을 설정하기 위해서는 §a닉네임권§7 한 장이 필요합니다.");
			return;
		}
		$regex = "/§[0-9a-fk-or]/";
		$matches = [];
		preg_match_all($regex, $nickname, $matches);
		if(str_contains($nickname, "§l") || count($matches[0]) > 0){
			$player->sendMessage(Loader::$prefix . "닉네임에는 색을 사용할 수 없습니다.");
			return;
		}
		$player->getInventory()->removeItem($item);
		$this->session->setNickname($nickname);
		$player->sendMessage(Loader::$prefix . "내 닉네임을 " . $nickname . "§r§7으(로) 설정했습니다!");
	}
}
