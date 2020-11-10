<?php
namespace xxAROX\StimoCommunity;
use pocketmine\event\block\BlockBreakEvent;
use xxAROX\StimoCommunity\item\ActionItem;


/**
 * Class Listener
 * @package xxAROX\StimoCommunity
 * @author xxAROX
 * @date 10.11.2020 - 04:34
 * @project StimoCommunity
 */
class Listener implements \pocketmine\event\Listener{
	/**
	 * Function onBreak
	 * @param BlockBreakEvent $event
	 * @return void
	 * @priority HIGHEST
	 */
	public function onBreak(BlockBreakEvent $event): void{
		$player = $event->getPlayer();
		$item = $event->getItem();
		$nbt = $item->getNamedTag();

		if ($item instanceof ActionItem) {
			/** @var ActionItem $item */
			$item->onBreak($player, $event->getBlock());
			$event->setCancelled(true);
		}
	}
}
