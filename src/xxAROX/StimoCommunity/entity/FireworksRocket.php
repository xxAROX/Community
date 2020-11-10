<?php
namespace xxAROX\StimoCommunity\entity;
use pocketmine\entity\Entity;
use pocketmine\item\Item;
use pocketmine\level\Level;
use pocketmine\level\Position;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\ActorEventPacket;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use xxAROX\StimoCommunity\item\Firework;


/**
 * Class FireworksRocket
 * @package xxAROX\StimoCommunity\entity
 * @author xxAROX
 * @date 10.11.2020 - 04:16
 * @project StimoCommunity
 */
class FireworksRocket extends Entity{
	const NETWORK_ID = self::FIREWORKS_ROCKET;
	public $width = 0.25;
	public $height = 0.25;
	const DATA_FIREWORK_ITEM = 16;
	/** @var int */
	protected $lifeTime = 0;

	/**
	 * FireworksRocket constructor.
	 * @param Level $level
	 * @param CompoundTag $nbt
	 * @param Firework $fireworkItem
	 */
	public function __construct(Level $level, CompoundTag $nbt, Firework $fireworkItem){
		parent::__construct($level, $nbt);
		if ($fireworkItem !== null && $fireworkItem->getNamedTagEntry("Fireworks") instanceof CompoundTag) {
			$this->propertyManager->setCompoundTag(self::DATA_FIREWORK_ITEM, $fireworkItem->getNamedTag());
			$this->setLifeTime($fireworkItem->getRandomizedFlightDuration());
		}
		$level->broadcastLevelSoundEvent($this, LevelSoundEventPacket::SOUND_LAUNCH);
	}

	/**
	 * Function tryChangeMovement
	 * @return void
	 */
	protected function tryChangeMovement(): void{
		$this->motion->x *= 1.15;
		$this->motion->y += 0.04;
		$this->motion->z *= 1.15;
	}

	/**
	 * Function entityBaseTick
	 * @param int $tickDiff
	 * @return bool
	 */
	public function entityBaseTick(int $tickDiff = 1): bool{
		if ($this->closed) {
			return false;
		}
		$hasUpdate = parent::entityBaseTick($tickDiff);
		if ($this->doLifeTimeTick()) {
			$hasUpdate = true;
		}
		return $hasUpdate;
	}

	/**
	 * Function setLifeTime
	 * @param int $life
	 * @return void
	 */
	public function setLifeTime(int $life): void{
		$this->lifeTime = $life;
	}

	/**
	 * Function doLifeTimeTick
	 * @return bool
	 */
	protected function doLifeTimeTick(): bool{
		if (!$this->isFlaggedForDespawn() and --$this->lifeTime < 0) {
			$this->doExplosionAnimation();
			$this->flagForDespawn();
			return true;
		}
		return false;
	}

	/**
	 * Function doExplosionAnimation
	 * @return void
	 */
	protected function doExplosionAnimation(): void{
		$this->broadcastEntityEvent(ActorEventPacket::FIREWORK_PARTICLES);
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
		/** @var Firework $item */
		$item = Item::get(Item::FIREWORKS, 0, 1);
		$item->addExplosion($type, $color, $fade, $flicker, $trail);

		$nbt = Entity::createBaseNBT($position, new Vector3(0.001, 0.05, 0.001), lcg_value() * 360, 90);
		$entity = Entity::createEntity("FireworksRocket", $position->getLevel(), $nbt, $item);

		if ($entity instanceof Entity) {
			$entity->spawnToAll();
		}
	}
}
