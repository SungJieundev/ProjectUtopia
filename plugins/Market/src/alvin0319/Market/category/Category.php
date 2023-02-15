<?php

declare(strict_types=1);

namespace alvin0319\Market\category;

use JsonSerializable;
use function count;

final class Category implements JsonSerializable{

	public const MARKET_PER_PAGE = 45;

	/** @var PageData[] */
	private array $pages = [];

	/** @param array<int, array<int, int>> $pages */
	public function __construct(private readonly string $name, array $pages){
		foreach($pages as $pageInt => $pageData){
			$this->pages[$pageInt] = new PageData($pageData);
		}
	}

	public function getName() : string{
		return $this->name;
	}

	public function getPage(int $page) : PageData{
		return $this->pages[$page] ??= new PageData([]);
	}

	public function getMaxPage() : int{
		foreach($this->pages as $int => $page){
			if(count($page->getMarkets()) === 0){
				unset($this->pages[$int]);
			}
		}
		return count($this->pages);
	}

	/** @return array<string, mixed> */
	public function jsonSerialize() : array{
		$pages = [];
		foreach($this->pages as $pageInt => $page){
			$pages[$pageInt] = $page->jsonSerialize();
		}
		return [
			"name" => $this->name,
			"pages" => $pages
		];
	}

	/** @param array<string, mixed> $data */
	public static function jsonDeserialize(array $data) : Category{
		return new Category($data["name"], $data["pages"]);
	}
}
