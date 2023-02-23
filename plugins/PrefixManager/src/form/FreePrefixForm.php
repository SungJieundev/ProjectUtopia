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

final readonly class FreePrefixForm implements Form{

	public function __construct(public PrefixSession $session, public Player $player){ }

	/** @phpstan-return CustomForm */
	public function jsonSerialize() : array{
		return [
			"type" => "custom_form",
			"title" => "§l자유칭호 사용",
			"content" => [
				[
					"type" => "input",
					"text" => "사용할 칭호를 입력해주세요. ('['와 ']'는 자동으로 추가되므로 사용할 필요가 없습니다.)",
					"placeholder" => "멋쟁이",
				]
			]
		];
	}

	public function handleResponse(Player $player, $data) : void{
		if(!is_array($data) || count($data) !== 1){
			return;
		}
		$prefix = $data[0];
		if(trim($prefix) === ""){
			return;
		}
		if(mb_strlen($prefix, "utf8") > 7){
			$player->sendMessage(Loader::$prefix . "칭호는 7자 이하로 설정 가능합니다.");
			return;
		}
		$item = (clone Loader::$freePrefixItem)->setCount(1);
		if(!$player->getInventory()->contains($item)){
			$player->sendMessage(Loader::$prefix . "칭호 변경권을 소지하고 있지 않습니다.");
			return;
		}
		$player->getInventory()->removeItem($item);
		$session = $this->session;
		$existingPrefix = Loader::getInstance()->prefixManager->getPrefixByName("§b[§r{$prefix}§b] §r§7");
		if($existingPrefix !== null){
			$session->addPrefix($existingPrefix);
		}else{
			Loader::getInstance()->prefixManager->addPrefix("§b[§r{$prefix}§b] §r§7");
		}
	}
}