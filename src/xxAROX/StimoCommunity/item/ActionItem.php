<?php
namespace xxAROX\StimoCommunity\item;
use pocketmine\block\Block;
use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\Player;
use pocketmine\utils\Utils;
use xxAROX\StimoCommunity\StimoCommunity;


/**
 * Class ActionItem
 * @package xxAROX\StimoCommunity\item
 * @author xxAROX
 * @date 10.11.2020 - 06:42
 * @project StimoCommunity
 */
class ActionItem extends NBT{
	private $callable;


	/**
	 * ActionItem constructor.
	 * @param string $nbtTag
	 */
	public function __construct(string $nbtTag){
		parent::__construct(self::GOLD_SHOVEL, 0, "Action Item");

		$nbt = $this->getNamedTag();
		$nbt->setInt($nbtTag, 1);
		$this->setNamedTag($nbt);

		switch ($nbtTag) {
			case self::TAG_SET_POSITION:
				$this->setCustomName("§r§dSet Position");
				$this->setLore(["§r§5RIGHT-CLICK §8» §7Set position 1.","§r§5LEFT-CLICK  §8» §7Set position 2."]);
				$callable = function (Player $player, Block $clicked, bool $place): void{
					$stage = StimoCommunity::getStage();
					if ($place) {
						$stage->setPos1($clicked->asPosition());
						$player->sendMessage(StimoCommunity::PREFIX . "Position 1 selected");
					} else {
						$stage->setPos2($clicked->asPosition());
						$player->sendMessage(StimoCommunity::PREFIX . "Position 2 selected");
					}
				};
				break;
			case self::TAG_SET_FIREWORK:
				$this->setCustomName("§r§dFireworks");
				$callable = function (Player $player, Block $clicked, bool $place): void{
					$stage = StimoCommunity::getStage();

					if ($stage->isFireworkPosition($clicked->asVector3())) {
						$stage->removeFireworkPosition($clicked->asVector3());
						$player->sendMessage(StimoCommunity::PREFIX . "Firework position was removed.");
					} else {
						$stage->addFireworkPosition($clicked->asVector3());
						$player->sendMessage(StimoCommunity::PREFIX . "Firework position was created.");
					}
				};
				break;
			case self::TAG_SET_VULKAN:
				$this->setCustomName("§r§dVulkan");
				$callable = function (Player $player, Block $clicked, bool $place): void{
					$stage = StimoCommunity::getStage();

					if ($stage->isVulkanPosition($clicked->asVector3())) {
						$stage->removeVulkanPosition($clicked->asVector3());
						$player->sendMessage(StimoCommunity::PREFIX . "Vulkan position was removed.");
					} else {
						$stage->addVulkanPosition($clicked->asVector3());
						$player->sendMessage(StimoCommunity::PREFIX . "Vulkan position was created.");
					}
				};
				break;
			default:
				$this->setCustomName("§r§4ERROR");
				$callable = function (Player $player, Block $clicked): void{
					$player->sendMessage(StimoCommunity::PREFIX . "§4Error");
				};
				break;
		}
		Utils::validateCallableSignature(function (Player $player, Block $clicked, bool $place): void{}, $callable);
		$this->callable = $callable;
	}

	/**
	 * Function getCooldownTicks
	 * @return int
	 */
	public function getCooldownTicks(): int{
		return 20 * 1;
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
		if (!$player->hasItemCooldown($this)) {
			$player->resetItemCooldown($this);
			if ($blockReplace->getId() == 0) {
				$blockReplace = $blockClicked;
			}
			($this->callable)($player, $blockReplace, true);
		}
		return false;
	}

	public function onBreak(Player $player, Block $block): void{
		($this->callable)($player, $block, false);
	}
}
