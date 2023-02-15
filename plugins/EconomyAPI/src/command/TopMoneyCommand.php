<?php

declare(strict_types=1);

namespace alvin0319\EconomyAPI\command;

use alvin0319\EconomyAPI\EconomyAPI;
use pocketmine\command\CommandSender;
use SOFe\AwaitGenerator\Await;
use function array_shift;
use function ceil;
use function count;
use function is_numeric;

final class TopMoneyCommand extends BaseEconomyCommand{

	public function __construct(){
		parent::__construct("돈순위", "돈 순위를 확인합니다.", "", ["topmoney"]);
		$this->setPermission("economyapi.command.topmoney");
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args) : void{
		if(!$this->testPermission($sender)){
			return;
		}
		$page = 1;
		$currency = $this->plugin->getDefaultCurrency();
		if(count($args) > 0){
			$temp = array_shift($args);
			if(is_numeric($temp)){
				$page = (int) $temp;
			}
		}
		if(count($args) > 0){
			$currencyName = array_shift($args);
			if($this->plugin->getCurrency($currencyName) !== null){
				$currency = $this->plugin->getCurrency($currencyName);
			}
		}
		Await::f2c(function() use ($sender, $currency, $page) : \Generator{
			$allRows = yield from EconomyAPI::$database->economyapiGetrows($currency->getName());
			$maxPage = (int) ceil($allRows[0]["columns"] / 5);
			if($page > $maxPage){
				$page = $maxPage;
			}
			$rows = yield from EconomyAPI::$database->economyapiTop($currency->getName(), ($page - 1) * 5);
			if(count($rows) < 1){
				$sender->sendMessage(EconomyAPI::$prefix . "잘못된 페이지입니다.");
			}else{
				$sender->sendMessage(EconomyAPI::$prefix . "돈 순위 (전체 $maxPage 페이지중 $page 페이지)");
				$i = 0;
				foreach($rows as $row){
					$i++;
					$rank = (($page === 0 ? 1 : $page) - 1) * 5 + $i;
					$sender->sendMessage(EconomyAPI::$prefix . "[{$rank}위] {$row["name"]}: {$currency->format($row["money"])}");
				}
			}
		});
	}
}
