<?php

declare(strict_types=1);

namespace alvin0319\PrefixManager\form;

use alvin0319\PrefixManager\Loader;
use alvin0319\PrefixManager\session\PrefixSession;
use pocketmine\form\Form;
use pocketmine\item\VanillaItems;
use pocketmine\player\Player;
use function count;
use function preg_match_all;
use function str_contains;

final class MakePrefixForm implements Form{

	public function __construct(private PrefixSession $session){ }

	/** @return array<string, mixed> */
	public function jsonSerialize() : array{
		return [
			"type" => "custom_form",
			"title" => "§l칭호 만들기",
			"content" => [
				[
					"type" => "label",
					"text" => "§l§c※ 주의사항§r§f\n- 칭호는 최대 §a5글자§f까지 입력할 수 있습니다.\n- 칭호는 앞과 뒤에 \"[\", \"]\"가 필요하지 않습니다. (자동으로 추가됨)\n- 추가하면 되돌릴 수 없습니다.\n- 칭호에는 한 가지 색만 사용할 수 있습니다. (굵기 X)"
				],
				[
					"type" => "input",
					"text" => "§l칭호 입력 (위의 주의사항을 꼭 읽어주세요.)"
				]
			]
		];
	}

	public function handleResponse(Player $player, $data) : void{
		if($data === null || count($data) !== 2){
			return;
		}
		[, $rawPrefix] = $data;
		$prefix = "§6[ §f{$rawPrefix} §6]§r";
		$item = VanillaItems::PAPER()
			->setCount(1);
		$item->getNamedTag()->setByte(Loader::TAG_FREE_PREFIX_ITEM, 1);
		if(!$player->getInventory()->contains($item)){
			$player->sendMessage(Loader::$prefix . "자유 칭호를 만들기 위해서는 §a자유칭호권§7 한 장이 필요합니다.");
			return;
		}
		// make a regex that get all minecraft color code
		$regex = "/§[0-9a-fk-or]/";
		$matches = [];
		preg_match_all($regex, $rawPrefix, $matches);
		if(str_contains($rawPrefix, "§l") || count($matches[0]) > 1){
			$player->sendMessage(Loader::$prefix . "칭호에는 한 가지 색만 사용할 수 있습니다.");
			return;
		}
		if($this->session->hasPrefix($prefix)){
			$player->sendMessage(Loader::$prefix . "해당 칭호를 이미 소유하고 있습니다.");
			return;
		}
		$player->getInventory()->removeItem($item);
		$this->session->addPrefix($prefix);
		$player->sendMessage(Loader::$prefix . $prefix . "§r§7 칭호를 추가했습니다!");
	}
}
