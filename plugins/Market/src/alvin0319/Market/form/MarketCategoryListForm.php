<?php

declare(strict_types=1);

namespace alvin0319\Market\form;

use alvin0319\Market\category\Category;
use alvin0319\Market\listener\InventoryListener;
use pocketmine\form\Form;
use pocketmine\player\Player;
use function array_map;
use function is_int;

final readonly class MarketCategoryListForm implements Form{

	/** @param Category[] $categories */
	public function __construct(private array $categories){}

	/** @phpstan-return SimpleForm */
	public function jsonSerialize() : array{
		return [
			"type" => "form",
			"title" => "§l카테고리 선택",
			"content" => "",
			"buttons" => array_map(static fn(Category $category) => ["text" => $category->getName()], $this->categories)
		];
	}

	public function handleResponse(Player $player, $data) : void{
		if(!is_int($data)){
			return;
		}
		if(!isset($this->categories[$data])){
			return;
		}
		$category = $this->categories[$data];
		InventoryListener::getInstance()->sendCategory($player, $category);
	}
}
