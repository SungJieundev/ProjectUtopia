<?php

declare(strict_types=1);

namespace alvin0319\Weather\command;

use alvin0319\Weather\Loader;
use alvin0319\Weather\season\Weather;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\PluginOwned;
use pocketmine\plugin\PluginOwnedTrait;
use function alvin0319\ExtensionPlugin\assumeNotNull;
use function array_shift;
use function count;

final class WeatherCommand extends Command implements PluginOwned{
	use PluginOwnedTrait;

	public function __construct(){
		parent::__construct("날씨", "날씨를 변경합니다.");
		$this->setPermission("weather.command");
		$this->owningPlugin = Loader::getInstance();
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args) : void{
		if(!$this->testPermission($sender)){
			return;
		}
		if(!$sender instanceof Player){
			$sender->sendMessage(Loader::$prefix . "인게임에서만 사용할 수 있습니다.");
			return;
		}
		if(count($args) < 1){
			$sender->sendMessage(Loader::$prefix . "사용법: /날씨 <맑음|비|눈|천둥> [월드]");
			return;
		}
		$weather = match(assumeNotNull(array_shift($args))){
			"맑음" => Weather::SUNNY(),
			"비" => Weather::RAINY(),
			"눈" => Weather::SNOWY(),
			"천둥" => Weather::THUNDER(),
			default => null
		};
		if($weather === null){
			$sender->sendMessage(Loader::$prefix . "잘못된 날씨입니다.");
			return;
		}
		$world = $sender->getWorld();
		if(count($args) > 0){
			$worldName = assumeNotNull(array_shift($args));
			if(($world = Loader::getInstance()->getServer()->getWorldManager()->getWorldByName($worldName)) === null){
				$sender->sendMessage(Loader::$prefix . "월드를 찾을 수 없습니다.");
				return;
			}
		}
		foreach($world->getPlayers() as $player){
			Weather::clearWeather($player);
			$weather->sendWeatherPacket($player);
		}
		$sender->sendMessage(Loader::$prefix . "날씨를 " . $weather->name() . "으(로) 변경했습니다.");
	}
}
