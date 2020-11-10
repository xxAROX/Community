<?php
namespace xxAROX\StimoCommunity\item;
use pocketmine\block\Block;
use pocketmine\entity\Entity;
use pocketmine\item\Item;
use pocketmine\level\Position;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\Player;


/**
 * Class Firework
 * @package xxAROX\StimoCommunity\item
 * @author xxAROX
 * @date 10.11.2020 - 04:20
 * @project StimoCommunity
 */
class Firework extends Item{
	public const TYPE_SMALL_SPHERE = 0;
	public const TYPE_HUGE_SPHERE  = 1;
	public const TYPE_STAR         = 2;
	public const TYPE_CREEPER_HEAD = 3;
	public const TYPE_BURST        = 4;
	//color = chr(dye metadata)
	public const COLOR_BLACK       = "\x00";
	public const COLOR_RED         = "\x01";
	public const COLOR_DARK_GREEN  = "\x02";
	public const COLOR_BROWN       = "\x03";
	public const COLOR_BLUE        = "\x04";
	public const COLOR_DARK_PURPLE = "\x05";
	public const COLOR_DARK_AQUA   = "\x06";
	public const COLOR_GRAY        = "\x07";
	public const COLOR_DARK_GRAY   = "\x08";
	public const COLOR_PINK        = "\x09";
	public const COLOR_GREEN       = "\x0a";
	public const COLOR_YELLOW      = "\x0b";
	public const COLOR_LIGHT_AQUA  = "\x0c";
	public const COLOR_DARK_PINK   = "\x0d";
	public const COLOR_GOLD        = "\x0e";
	public const COLOR_WHITE       = "\x0f";

	/**
	 * Firework constructor.
	 * @param int $meta
	 */
	public function __construct(int $meta = 0){
		parent::__construct(self::FIREWORKS, $meta, "Fireworks");
	}

	/**
	 * Function getFlightDuration
	 * @return int
	 */
	public function getFlightDuration(): int{
		return $this->getExplosionsTag()->getByte("Flight", 1);
	}

	/**
	 * Function getRandomizedFlightDuration
	 * @return int
	 */
	public function getRandomizedFlightDuration(): int{
		return ($this->getFlightDuration() + 1) * 10 + mt_rand(0, 5) + mt_rand(0, 6);
	}

	/**
	 * Function setFlightDuration
	 * @param int $duration
	 * @return void
	 */
	public function setFlightDuration(int $duration): void{
		$tag = $this->getExplosionsTag();
		$tag->setByte("Flight", $duration);
		$this->setNamedTagEntry($tag);
	}

	/**
	 * Function addExplosion
	 * @param int $type
	 * @param string $color
	 * @param string $fade
	 * @param bool $flicker
	 * @param bool $trail
	 * @return void
	 */
	public function addExplosion(int $type, string $color, string $fade = "", bool $flicker = false, bool $trail = false): void{
		$explosion = new CompoundTag();
		$explosion->setByte("FireworkType", $type);
		$explosion->setByteArray("FireworkColor", $color);
		$explosion->setByteArray("FireworkFade", $fade);
		$explosion->setByte("FireworkFlicker", $flicker ? 1 : 0);
		$explosion->setByte("FireworkTrail", $trail ? 1 : 0);

		$tag = $this->getExplosionsTag();
		$explosions = $tag->getListTag("Explosions") ?? new ListTag("Explosions");

		$explosions->push($explosion);
		$tag->setTag($explosions);
		$this->setNamedTagEntry($tag);
	}

	/**
	 * Function getExplosionsTag
	 * @return CompoundTag
	 */
	protected function getExplosionsTag(): CompoundTag{
		return $this->getNamedTag()->getCompoundTag("Fireworks") ?? new CompoundTag("Fireworks");
	}

	/**
	 * Function onActivate
	 * @param Player $player
	 * @param Block $blockReplace
	 * @param Block $blockClicked
	 * @param int $face
	 * @param Vector3 $clickVector
	 * @return bool
	 */
	public function onActivate(Player $player, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector): bool{
		$nbt = Entity::createBaseNBT($blockReplace->add(0.5, 0, 0.5), new Vector3(0.001, 0.05, 0.001), lcg_value() * 360, 90);
		$entity = Entity::createEntity("FireworksRocket", $player->getLevel(), $nbt, $this);

		if ($entity instanceof Entity) {
			if ($player->getGamemode() !== Player::CREATIVE) {
				$this->pop();
			}
			$entity->spawnToAll();
			return true;
		}
		return false;
	}

	/**
	 * Function spawn
	 * @param Position $position
	 * @param int $type
	 * @param string $color
	 * @param string $fade
	 * @param bool $flicker
	 * @param bool $trail
	 * @return void
	 */
	static function spawn(Position $position, int $type, string $color, string $fade = "", bool $flicker = false, bool $trail = false): void{
		/** @var self $item */
		$item = Item::get(self::FIREWORKS, 0, 1);
		$item->addExplosion($type, $color, $fade, $flicker, $trail);

		$nbt = Entity::createBaseNBT($position, new Vector3(0.001, 0.05, 0.001), lcg_value() * 360, 90);
		$entity = Entity::createEntity("FireworksRocket", $position->getLevel(), $nbt, $item);

		if ($entity instanceof Entity) {
			$entity->spawnToAll();
		}
	}
}
