<?php

declare(strict_types=1);

namespace alvin0319\PrefixManager\session;

use alvin0319\PrefixManager\chat\PrefixChatFormatter;
use alvin0319\PrefixManager\Loader;
use Generator;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use pocketmine\utils\Utils;
use SOFe\AwaitGenerator\Await;
use function array_filter;
use function array_values;
use function count;
use function json_decode;
use function json_encode;

final class PrefixSession{

	private bool $loaded = false;

	private string $nickname = "";

	private int $selectedPrefixIndex = 0;
	/** @var array<int, string> */
	private array $prefixes = [];

	private int $syncBlocked = 0;

	private ?Player $player = null;

	private int $offlineTick = 0;

	public PrefixChatFormatter $formatter;

	public function __construct(private readonly Loader $plugin, private readonly string $name, ?Player $player = null){
		$this->player = $player;
		if($player !== null){
			$this->formatter = new PrefixChatFormatter($player, $this);
		}
	}

	public function getPlayer() : ?Player{
		return $this->player ??= $this->plugin->getServer()->getPlayerExact($this->name);
	}

	public function getName() : string{
		return $this->name;
	}

	public function getNickname() : string{
		return $this->nickname;
	}

	public function getPlugin() : Loader{
		return $this->plugin;
	}

	/** @return string[] */
	public function getPrefixes() : array{
		return $this->prefixes;
	}

	public function isLoaded() : bool{
		return $this->loaded;
	}

	public function getSelectedPrefix() : string{
		return $this->prefixes[$this->selectedPrefixIndex];
	}

	public function tick() : void{
		if(!$this->loaded){
			Await::f2c(function() : Generator{
				$rows = yield from Loader::$database->get($this->name);
				if(count($rows) > 0){
					if($rows[0]["syncBlocked"] === 1){
						if(++$this->syncBlocked < 3){
							return;
						}
						$this->syncBlocked = 0;
						$this->plugin->getLogger()->debug("Forced to unblock sync for player " . $this->name);
					}
					$selectedPrefix = $rows[0]["selectedPrefix"];
					$prefixes = json_decode($rows[0]["prefixes"], true);
					$this->selectedPrefixIndex = $selectedPrefix;
					$this->nickname = $rows[0]["nickname"];
				}else{
					$defaultPrefixes = json_encode($prefixes = Utils::assumeNotFalse($this->plugin->getConfig()->get("default-prefixes", [])));
					$this->nickname = $this->getPlayer() !== null ? $this->getPlayer()->getName() : $this->name;
					yield from Loader::$database->create($this->name, $this->getPlayer() !== null ? $this->getPlayer()->getName() : $this->name, 0, Utils::assumeNotFalse($defaultPrefixes), 0);
				}
				$this->prefixes = $prefixes;
				yield from Loader::$database->updateState($this->name, 1);
				$this->plugin->getLogger()->debug("Loaded prefixes for player " . $this->name);
				$this->loaded = true;
			});
		}
		if($this->getPlayer() === null){
			if(++$this->offlineTick >= 60){
				$this->plugin->removeSession($this->name);
			}
		}
	}

	public function switchOnline(Player $player) : void{
		$this->player = $player;
		$this->offlineTick = 0;
	}

	public function getSelectedPrefixIndex() : int{
		return $this->selectedPrefixIndex;
	}

	public function setSelectedPrefixIndex(int $index) : void{
		$this->selectedPrefixIndex = $index;
	}

	public function addPrefix(string $prefix) : void{
		$this->prefixes[] = $prefix;
	}

	public function removePrefix(string $prefix) : void{
		$this->prefixes = array_values(array_filter($this->prefixes, function(string $p) use ($prefix) : bool{
			return $p !== $prefix;
		}));
	}

	public function setNickname(string $nickname) : void{
		$this->nickname = $nickname;
	}

	public function hasPrefix(string $prefix) : bool{
		$prefix = TextFormat::clean($prefix);
		foreach($this->prefixes as $str){
			if(TextFormat::clean($str) === $prefix){
				return true;
			}
		}
		return false;
	}

	public function save() : void{
		if($this->loaded){
			Await::f2c(function() : Generator{
				yield from Loader::$database->update($this->name, $this->nickname, $this->selectedPrefixIndex, Utils::assumeNotFalse(json_encode($this->prefixes)), 0);
			});
		}
	}
}
