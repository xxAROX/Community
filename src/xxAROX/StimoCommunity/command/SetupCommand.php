<?php
namespace xxAROX\StimoCommunity\command;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\Item;
use pocketmine\Player;
use xxAROX\StimoCommunity\item\ActionItem;
use xxAROX\StimoCommunity\item\NBT;
use xxAROX\StimoCommunity\StimoCommunity;


/**
 * Class SetupCommand
 * @package xxAROX\StimoCommunity\command
 * @author xxAROX
 * @date 10.11.2020 - 05:46
 * @project StimoCommunity
 */
class SetupCommand extends Command{
	/**
	 * SetupCommand constructor.
	 * @param string $name
	 */
	public function __construct(string $name){
		parent::__construct($name, "Setup community stage", null, []);
		$this->setPermission("xxarox.command.community.setup");
	}

	/**
	 * Function execute
	 * @param CommandSender $sender
	 * @param string $commandLabel
	 * @param array $args
	 * @return mixed|void
	 */
	public function execute(CommandSender $sender, string $commandLabel, array $args){
		if (!$this->testPermission($sender)) {
			return;
		}
		if (!$sender instanceof Player) {
			return;
		}
		$sender->getInventory()->clearAll();
		$items = [
			new ActionItem(ActionItem::TAG_SET_POSITION),
			new ActionItem(ActionItem::TAG_SET_FIREWORK),
			new ActionItem(ActionItem::TAG_SET_VULKAN),
		];
		$i = 0;
		foreach ($items as $item) {
			$sender->getInventory()->setItem($i, $item);
			$i++;
		}
	}
}
