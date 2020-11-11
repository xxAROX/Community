<?php
namespace xxAROX\StimoCommunity\item;
use pocketmine\item\Item;


/**
 * Class NBT
 * @package xxAROX\StimoCommunity\item
 * @author xxAROX
 * @date 10.11.2020 - 06:31
 * @project StimoCommunity
 */
abstract class NBT extends Item{
	const TAG_SET_POSITION   = "xxarox:stage:setposition";
	const TAG_SET_FIREWORK   = "xxarox:stage:setfirework";
	const TAG_SET_VULKAN     = "xxarox:stage:setvulkan";
	const TAG_SET_MUSIC_DESK = "xxarox:stage:musicdesk";
	const TAG_SET_SETTINGS   = "xxarox:stage:settings";
	const TAG_SET_MUSIC_BOX  = "xxarox:stage:musicbox";
}
