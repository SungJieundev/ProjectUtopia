<?php

declare(strict_types=1);

namespace alvin0319\PrefixManager\form;

use alvin0319\PrefixManager\Loader;
use alvin0319\PrefixManager\session\PrefixSession;
use pocketmine\form\Form;
use pocketmine\player\Player;
use function count;
use function is_array;
use function mb_strlen;
use function trim;

final readonly class NickNameForm implements Form{

	public function __construct(public PrefixSession $session, public Player $player){ }

	/** @phpstan-return CustomForm */
	public function jsonSerialize() : array{
		return [
			"type" => "custom_form",
			"title" => "§l닉네임 변경",
			"content" => [
				[
					"type" => "input",
					"text" => "변경할 닉네임"
				]
			]
		];
	}

	public function handleResponse(Player $player, $data) : void{
		if(!is_array($data) || count($data) !== 1){
			return;
		}
		$nickName = $data[0];
		if(trim($nickName) === ""){
			return;
		}
		if(mb_strlen($nickName, "utf8") > 6){
			$player->sendMessage(Loader::$prefix . "닉네임은 6자 이하로 설정 가능합니다.");
			return;
		}
		$item = (clone Loader::$nicknameItem)->setCount(1);
		if(!$player->getInventory()->contains($item)){
			$player->sendMessage(Loader::$prefix . "닉네임 변경권을 소지하고 있지 않습니다.");
			return;
		}
		$player->getInventory()->removeItem($item);
		$session = $this->session;
		$session->setCustomName($nickName);
	}
}
