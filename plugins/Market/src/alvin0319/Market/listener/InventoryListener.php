<?php

declare(strict_types=1);

namespace alvin0319\Market\listener;

use alvin0319\EconomyAPI\EconomyAPI;
use alvin0319\EconomyAPI\util\Transaction;
use alvin0319\EconomyAPI\util\TransactionResult;
use alvin0319\EconomyAPI\util\TransactionType;
use alvin0319\Market\category\Category;
use alvin0319\Market\form\MarketBuyForm;
use alvin0319\Market\Loader;
use muqsit\invmenu\InvMenu;
use muqsit\invmenu\transaction\InvMenuTransaction;
use muqsit\invmenu\transaction\InvMenuTransactionResult;
use muqsit\invmenu\type\InvMenuTypeIds;
use pocketmine\block\VanillaBlocks;
use pocketmine\item\VanillaItems;
use pocketmine\player\Player;
use pocketmine\utils\SingletonTrait;
use function array_keys;
use function array_map;
use function array_values;
use function count;
use function implode;

final class InventoryListener{
	use SingletonTrait;

	public const TAG_PREVIOUS_PAGE = "previousPage";
	public const TAG_NEXT_PAGE = "nextPage";

	public const TAG_SELLALL = "sellAll";

	public const TAG_EDIT_MARKETS = "editMarkets";

	public const TAG_MARKET = "market";

	public const TAG_INFO = "info";

	/** @var InvMenu[] */
	private array $menus = [];

	/** @var int[] */
	private array $pages = [];

	/** @var Category[] */
	private array $categories = [];

	private function __construct(){
		self::setInstance($this);
	}

	public function sendCategory(Player $player, Category $category, int $page = 1, ?InvMenu $menu = null) : void{
		$send = $menu === null;
		if($menu === null){
			$menu = InvMenu::create(InvMenuTypeIds::TYPE_DOUBLE_CHEST);
			$menu->setName($category->getName());
		}else{
			$menu->getInventory()->clearAll();
		}
		$pageData = $category->getPage($page);
		foreach($pageData->getMarkets() as $slot => $market){
			$item = $market->getItem()->setCount(1);
			$prices = $market->toText(true, "§b");
			$item->setCustomName("§r§f" . $item->getName() . "\n\n§r§6[!] §f구매가: §b" . $prices[0]
				. "§r\n§6[!] §f판매가: §b" . $prices[1]
			);
			$item->getNamedTag()->setInt(self::TAG_MARKET, $market->getId());
			$menu->getInventory()->setItem($slot, $item);
		}
		// 48, 50: page change
		// 49: information
		// 53: sell all
		// 45: for op, change market item
		$pageChangeItem = VanillaItems::PAPER()->setCustomName("§6[!] §r§f");
		$previousItem = (clone $pageChangeItem)->setCustomName($pageChangeItem->getCustomName() . "이전 페이지");
		$previousItem->getNamedTag()->setByte(self::TAG_PREVIOUS_PAGE, 1);
		$nextItem = (clone $pageChangeItem)->setCustomName($pageChangeItem->getCustomName() . "다음 페이지");
		$nextItem->getNamedTag()->setByte(self::TAG_NEXT_PAGE, 1);
		$menu->getInventory()->setItem(48, $previousItem);
		$menu->getInventory()->setItem(50, $nextItem);

		$information = VanillaBlocks::CHEST()->asItem()->setCustomName("§r§6§l[!] §r§f{$page} / {$category->getMaxPage()}");
		$information->getNamedTag()->setByte(self::TAG_INFO, 1);
		$menu->getInventory()->setItem(49, $information);

		$sellAll = VanillaBlocks::BARRIER()->asItem()->setCustomName("§6§l[!] §r§f모두 판매하기");
		$sellAll->getNamedTag()->setByte(self::TAG_SELLALL, 1);
		$menu->getInventory()->setItem(53, $sellAll);

		if($player->hasPermission("market.bypass")){
			$editMarket = VanillaItems::IRON_PICKAXE()->setCustomName("§6§l[!] §r§f카테고리 수정");
			$editMarket->getNamedTag()->setByte(self::TAG_EDIT_MARKETS, 1);
			$menu->getInventory()->setItem(45, $editMarket);
		}

		if($send){
			$menu->setInventoryCloseListener(function(Player $player) : void{
				if(isset($this->menus[$player->getName()])){
					unset($this->menus[$player->getName()]);
				}
				if(isset($this->pages[$player->getName()])){
					unset($this->pages[$player->getName()]);
				}
				if(isset($this->categories[$player->getName()])){
					unset($this->categories[$player->getName()]);
				}
			});

			$menu->setListener($this->handleCategory(...));

			$menu->send($player, $category->getName(), function(bool $success) use ($player, $menu, $category) : void{
				$this->menus[$player->getName()] = $menu;
				$this->pages[$player->getName()] = 1;
				$this->categories[$player->getName()] = $category;
			});
		}
	}

	public function handleCategory(InvMenuTransaction $action) : InvMenuTransactionResult{
		$player = $action->getPlayer();
		if(!isset($this->menus[$player->getName()])){
			return $action->discard();
		}
		$menu = $this->menus[$player->getName()];
		$category = $this->categories[$player->getName()];
		$item = $action->getOut();
		$currentPage = $this->pages[$player->getName()];

		if($item->getNamedTag()->getByte(self::TAG_PREVIOUS_PAGE, -1) !== -1){
			$page = $this->pages[$player->getName()] - 1;
			if($page <= 0){
				return $action->discard();
			}
			$this->pages[$player->getName()] = $page;
			$this->sendCategory($player, $this->categories[$player->getName()], $page, $menu);
			return $action->discard();
		}
		if($item->getNamedTag()->getByte(self::TAG_NEXT_PAGE, -1) !== -1){
			$page = $this->pages[$player->getName()] + 1;
			if($page > $category->getMaxPage() && !$player->hasPermission("market.bypass")){
				return $action->discard();
			}
			$this->pages[$player->getName()] = $page;
			$this->sendCategory($player, $this->categories[$player->getName()], $page, $menu);
			return $action->discard();
		}
		if($item->getNamedTag()->getByte(self::TAG_SELLALL, -1) !== -1){
			$menu->onClose($player);
			$pageData = $category->getPage($currentPage);
			$soldResult = [];
			$earn = [];
			foreach($player->getInventory()->getContents(false) as $item){
				$market = Loader::getInstance()->getMarketManager()->getMarketByItem($item);
				if($market === null){
					continue;
				}
				$currency = $market->getCurrency();
				if(!isset($soldResult[$curName = $currency->getName()])){
					$soldResult[$curName] = [];
				}
				if(!isset($earn[$curName])){
					$earn[$curName] = 0;
				}
				if(!$pageData->hasMarket($market)){
					continue;
				}
				$tx = new Transaction($player, $price = $market->getSellPrice() * $item->getCount(), TransactionType::ADD(), $currency);
				$result = $tx->execute();
				if(!$result->equals(TransactionResult::SUCCESS())){
					$player->sendMessage(Loader::$prefix . $result->getReason());
					continue;
				}
				$earn[$currency->getName()] += $price;
				$soldResult[$curName][$itemName = $item->getName()] = ($soldResult[$curName][$itemName] ?? 0) + $item->getCount();
				$player->getInventory()->removeItem($item);
			}
			if(count($earn) === 0){
				$player->sendMessage(Loader::$prefix . "판매할 아이템이 없습니다.");
				return $action->discard();
			}
//			foreach($earn as $currencyName => $money){
//				$currency = EconomyAPI::getInstance()->getCurrency($currencyName) ?? EconomyAPI::getInstance()->getDefaultCurrency();
//			}
//			$player->sendMessage(Loader::$prefix . "Sell all result: " . implode(", ", array_map(function(array $data) : string{
//					[$name, $soldCount] = [array_keys($data)[0], array_values($data)[0]];
//					return "§a$name x{$soldCount}§r§7";
//				}, $soldResult)));
			$player->sendMessage(Loader::$prefix . "수익: " . implode(", ", array_map(function(string $currencyName, int $money) : string{
					$currency = EconomyAPI::getInstance()->getCurrency($currencyName) ?? EconomyAPI::getInstance()->getDefaultCurrency();
					return $currency->format($money);
				}, array_keys($earn), array_values($earn))));
			return $action->discard();
		}
		if($item->getNamedTag()->getByte(self::TAG_EDIT_MARKETS, -1) !== -1){
			$this->sendMarketEdit($player, $category, $currentPage, $menu);
			return $action->discard();
		}
		if($item->getNamedTag()->getInt(self::TAG_MARKET, -1) !== -1){
			$market = Loader::getInstance()->getMarketManager()->getMarketById($item->getNamedTag()->getInt(self::TAG_MARKET, -1));
			// TODO: send form
			if($market === null){
				return $action->discard();
			}
			$menu->onClose($player);
			return $action->discard()->then(static function(Player $player) use ($market) : void{
				$player->sendForm(new MarketBuyForm($player, $market));
			});
		}
		return $action->discard();
	}

	public function sendMarketEdit(Player $player, Category $category, int $page, ?InvMenu $menu = null) : void{
		$send = $menu === null;
		if($menu === null){
			$menu = InvMenu::create(InvMenuTypeIds::TYPE_DOUBLE_CHEST);
		}else{
			$menu->getInventory()->clearAll();
		}

		$pageData = $category->getPage($page);

		foreach($pageData->getMarkets() as $slot => $market){
			$menu->getInventory()->setItem($slot, $market->getItem());
		}

		$menu->setListener(null);

		$menu->setInventoryCloseListener(function(Player $player) use ($menu, $pageData) : void{
			if(isset($this->menus[$player->getName()])){
				unset($this->menus[$player->getName()]);
			}
			if(isset($this->pages[$player->getName()])){
				unset($this->pages[$player->getName()]);
			}
			if(isset($this->categories[$player->getName()])){
				unset($this->categories[$player->getName()]);
			}
			$markets = [];
			foreach($menu->getInventory()->getContents(false) as $slot => $item){
				$market = Loader::getInstance()->getMarketManager()->getMarketByItem($item);
				if($market === null){
					$market = Loader::getInstance()->getMarketManager()->createMarket($item, -1, -1, EconomyAPI::getInstance()->getDefaultCurrency());
				}
				$markets[$slot] = $market;
			}
			$pageData->setMarkets($markets);
			$player->sendMessage(Loader::$prefix . "카테고리를 성공적으로 수정했습니다.");
		});

		if($send){
			$menu->send($player);
		}
	}
}
