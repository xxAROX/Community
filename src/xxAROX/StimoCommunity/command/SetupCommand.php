<?php
namespace xxAROX\StimoCommunity\command;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;


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
	}
}
