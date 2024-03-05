<?php
namespace SellExpUI\KienMC;

use pocketmine\Server;
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\player\Player;
use pocketmine\utils\Config;
use pocketmine\command\{Command, CommandSender};
use SellExpUI\KienMC\FormAPI\{Form, FormAPI, SimpleForm, ModalForm, CustomForm};
use DaPigGuy\libPiggyEconomy\libPiggyEconomy;

class Main extends PluginBase implements Listener {
	
	public $economyProvider;
	
	public function onEnable(): void{
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		$this->saveDefaultConfig();
		libPiggyEconomy::init();
        $this->economyProvider = libPiggyEconomy::getProvider($this->getConfig()->get("economy"));
		$this->getLogger()->info("Plugin SellExpUI Code By Kiên MC Is On Enable");
	}
	
	public function getEconomyProvider(){
		return $this->economyProvider;
	}
    
    public function onCommand(CommandSender $player, Command $cmd, string $label, array $args): bool {
		$name=$cmd->getName();
		if($name=="sellexpui"){
			if(!$player instanceof Player){
				$player->sendMessage("Use Command In Game!!");
				return true;
			}
			$this->menu($player);
			return true;
		}
		return true;
	}
	
	public function menu($player){
		$form = new CustomForm(function(Player $player, $data){
			if($data==null) return;
			if(!is_numeric($data[0])){
				$player->sendMessage("§l§c• You Must Be Enter Number");
				return;
			}
			if($data[0]<=0){
				$player->sendMessage("§l§c• The Number Must Be Bigger Than 0");
				return;
			}
			if($player->getXpManager()->getXpLevel()<$data[0]){
				$player->sendMessage("§l§c• You Dont Have Enough Experience To Sell");
				return 0;
			}
			$price = $this->getConfig()->get("price-sell");
			$player->getXpManager()->setXpLevel($player->getXpManager()->getXpLevel() - (int)($data[0]));
			$total=(int)((int)($price)*(int)($data[0]));
			$this->getEconomyProvider()->giveMoney($player, (int)($total));
			$player->sendMessage("§l§c•§a You Sold§e ".$data[0]." Experience Level§a And Received§e ".$total."$");
		});
		$price = $this->getConfig()->get("price-sell");
		$exp = $player->getXpManager()->getXpLevel();
		$form->setTitle("§l§c♦§e Sell Experience §c♦");
		$form->addInput("§l§c•§a Your Experience Level:§e ".$exp." Level\n§l§c•§a Price Sell:§e ".$price."$ / 1 Experience Level\n\n§l§c•§a Enter Number:");
		$form->sendToPlayer($player);
	}
}
