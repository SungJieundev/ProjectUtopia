<?php

declare(strict_types=1);

namespace alvin0319\Weather\season;

use pocketmine\utils\EnumTrait;
use pocketmine\utils\Utils;

/**
 * @method static Season SPRING()
 * @method static Season SUMMER()
 * @method static Season AUTUMN()
 * @method static Season WINTER()
 */
final class Season{
	use EnumTrait {
		EnumTrait::__construct as EnumTrait__Construct;
	}

	protected static function setup() : void{
		self::registerAll(
			new self("spring", [Weather::SUNNY(), Weather::RAINY()]),
			new self("summer", [Weather::SUNNY(), Weather::RAINY(), Weather::THUNDER()]),
			new self("autumn", [Weather::SUNNY(), Weather::RAINY()]),
			new self("winter", [Weather::SUNNY(), Weather::SNOWY()])
		);
	}

	/** @phpstan-var list<Weather> */
	public readonly array $availableWeathers;

	/** @phpstan-param list<Weather> $availableWeathers */
	public function __construct(string $enumName, array $availableWeathers){
		$this->EnumTrait__Construct($enumName);
		Utils::validateArrayValueType($availableWeathers, function(Weather $weather) : void{ });
		$this->availableWeathers = $availableWeathers;
	}

	public function hasWeather(Weather $weather) : bool{
		foreach($this->availableWeathers as $availableWeather){
			if($availableWeather->equals($weather)){
				return true;
			}
		}
		return false;
	}
}
