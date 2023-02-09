<?php

declare(strict_types=1);

namespace alvin0319\EconomyAPI\command;

use alvin0319\EconomyAPI\EconomyAPI;
use pocketmine\command\Command;
use pocketmine\lang\Translatable;
use pocketmine\plugin\PluginOwned;

abstract class BaseEconomyCommand extends Command implements PluginOwned{

	protected EconomyAPI $plugin;

	public function __construct(string $name, Translatable|string $description = "", Translatable|string|null $usageMessage = null, array $aliases = []){
		parent::__construct($name, $description, $usageMessage, $aliases);
		$this->plugin = EconomyAPI::getInstance();
	}

	public function getOwningPlugin() : EconomyAPI{
		return $this->plugin;
	}
}