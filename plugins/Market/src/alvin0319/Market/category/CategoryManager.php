<?php

declare(strict_types=1);

namespace alvin0319\Market\category;

use alvin0319\Market\Loader;
use pocketmine\utils\Utils;
use Symfony\Component\Filesystem\Path;
use function file_exists;
use function file_get_contents;
use function file_put_contents;
use function json_decode;
use function json_encode;
use const JSON_THROW_ON_ERROR;

final class CategoryManager{

	/** @var Category[] */
	private array $categories = [];

	public function __construct(private readonly Loader $plugin){
		if(file_exists($file = Path::join($this->plugin->getDataFolder(), "categories.json"))){
			$data = json_decode(Utils::assumeNotFalse(file_get_contents($file)), true, 512, JSON_THROW_ON_ERROR);
			foreach($data as $name => $categoryData){
				$category = Category::jsonDeserialize($categoryData);
				$this->categories[$category->getName()] = $category;
			}
		}
	}

	public function getCategory(string $name) : ?Category{
		return $this->categories[$name] ?? null;
	}

	/** @return Category[] */
	public function getCategories() : array{
		return $this->categories;
	}

	public function createCategory(string $name) : void{
		$this->categories[$name] = new Category($name, []);
	}

	public function removeCategory(string $name) : void{
		unset($this->categories[$name]);
	}

	public function save() : void{
		$data = [];
		foreach($this->categories as $category){
			$data[$category->getName()] = $category->jsonSerialize();
		}
		$this->plugin->saveResource("categories.json", true);
		file_put_contents(Path::join($this->plugin->getDataFolder(), "categories.json"), Utils::assumeNotFalse(json_encode($data, JSON_THROW_ON_ERROR)));
	}
}
