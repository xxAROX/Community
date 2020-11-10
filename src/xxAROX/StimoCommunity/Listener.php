<?php
namespace xxAROX\StimoCommunity;
use pocketmine\event\player\PlayerJoinEvent;


/**
 * Class Listener
 * @package xxAROX\StimoCommunity
 * @author xxAROX
 * @date 10.11.2020 - 04:34
 * @project StimoCommunity
 */
class Listener implements \pocketmine\event\Listener{
	public function onJoin(PlayerJoinEvent $event): void{
		$player = $event->getPlayer();
	}
}
