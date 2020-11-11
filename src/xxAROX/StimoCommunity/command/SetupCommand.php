<?php
namespace xxAROX\StimoCommunity\command;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use xxAROX\StimoCommunity\item\ActionItem;
use xxAROX\StimoCommunity\screenbox\Setup;
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
		if (!isset($args[0])) {
			$sender->sendMessage(StimoCommunity::PREFIX . "/{$this->getName()} <stage|screenbox>");
			return;
		}
		if (strtoupper($args[0]) == "stage") {
			$sender->getInventory()->clearAll();
			$items = [
				new ActionItem(ActionItem::TAG_SET_POSITION),
				new ActionItem(ActionItem::TAG_SET_FIREWORK),
				new ActionItem(ActionItem::TAG_SET_VULKAN),
				new ActionItem(ActionItem::TAG_SET_MUSIC_DESK),
				new ActionItem(ActionItem::TAG_SET_SETTINGS),
				new ActionItem(ActionItem::TAG_SET_MUSIC_BOX),
			];
			$i = 0;
			foreach ($items as $item) {
				$sender->getInventory()->setItem($i, $item);
				$i++;
			}
			return;
		}
		if (strtolower($args[0]) == "box" || strtolower($args[0]) == "screenbox") {
			Setup::addPlayer($sender);
			return;
		}
	}
}
