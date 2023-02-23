<?php

declare(strict_types=1);

namespace alvin0319\PrefixManager\form;

use alvin0319\PrefixManager\Loader;
use alvin0319\PrefixManager\prefix\Prefix;
use alvin0319\PrefixManager\session\PrefixSession;
use pocketmine\form\Form;
use pocketmine\player\Player;
use function array_map;
use function is_int;

final readonly class PrefixListForm implements Form{

	/** @var Prefix[] */
	private array $prefixes;

	public function __construct(public PrefixSession $session, public Player $player){ }

	/** @phpstan-return SimpleForm */
	public function jsonSerialize() : array{
		$this->prefixes = $this->session->getPrefixes();
		return [
			"type" => "form",
			"title" => "§l칭호 목록",
			"content" => "",
			"buttons" => array_map(static function(Prefix $prefix) : array{
				return ["text" => "- " . $prefix->prefix . ($this->session->getSelectedPrefix()->id === $prefix->id ? "\n§a착용한 칭호" : "")];
			}, $this->prefixes)
		];
	}

	public function handleResponse(Player $player, $data) : void{
		if(!is_int($data)){
			return;
		}
		if($this->session->getSelectedPrefix()->id === $this->prefixes[$data]->id){
			return;
		}
		$this->session->selectPrefix($data);
		$player->sendMessage(Loader::$prefix . "칭호를 " . $this->prefixes[$data]->prefix . "§r§7으(로) 변경했습니다.");
	}
}