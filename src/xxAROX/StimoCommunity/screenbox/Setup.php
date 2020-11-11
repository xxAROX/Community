<?php
namespace xxAROX\StimoCommunity\screenbox;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\Listener;
use pocketmine\level\Position;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\tile\Sign;
use pocketmine\utils\Config;
use pocketmine\utils\Utils;
use xxAROX\StimoCommunity\StimoCommunity;


/**
 * Class Setup
 * @package xxAROX\StimoCommunity\screenbox
 * @author xxAROX
 * @date 11.11.2020 - 04:47
 * @project StimoCommunity
 */
class Setup implements Listener{
	const STAGE_SIGN  = 0;
	const STAGE_SPAWN = 1;

	static $player = null;
	static $stage = 0;
	private static $config = [
		"signPos"       => null,
		"signLevelName" => null,
		"pos"           => null,
		"levelName"     => null,
	];

	/**
	 * Function addPlayer
	 * @param Player $player
	 * @return void
	 */
	static function addPlayer(Player $player): void{
		if (empty(self::$player)) {
			self::$player = $player->getName();
			self::$stage = 0;
			self::$config = [
				"signPos"       => null,
				"signLevelName" => null,
				"pos"           => null,
				"levelName"     => null,
			];
			$player->sendMessage(StimoCommunity::PREFIX . "§2Please break the sign for the screenbox!");
		} else {
			$player->sendMessage(StimoCommunity::PREFIX . "§2Another person is already setting up a screenbox, wait for it!");
		}
	}

	/**
	 * Function reset
	 * @return void
	 */
	static function reset(){
		self::$player = null;
		self::$stage = 0;
		self::$config = [
			"signPos"       => null,
			"signLevelName" => null,
			"pos"           => null,
			"levelName"     => null,
		];
	}

	/**
	 * Function nextStage
	 * @param Player $player
	 * @return void
	 */
	private function nextStage(Player $player): void{
		if (self::$player == $player->getName() && self::$stage == self::STAGE_SIGN) {
			self::$stage = self::STAGE_SPAWN;
			$player->sendMessage(StimoCommunity::PREFIX . "§2Please break the middle floor-block of the screenbox!");
			return;
		}
		if (self::$stage == self::STAGE_SPAWN) {
			self::$player = null;
			$screenBoxFile = new Config((Utils::getOS() == Utils::OS_LINUX ? "/home/.data/screenboxes.json" : StimoCommunity::getInstance()->getDataFolder() . "screenboxes.json"), Config::JSON, ["screenshotTime" => 30,"boxes" => []]);
			$data = $screenBoxFile->getAll();
			$data[] = self::$config;
			$screenBoxFile->setAll($data);
			$screenBoxFile->save();

			StimoCommunity::$screenBoxes = [];
			foreach ($data["boxes"] as $key => $value) {
				try {
					$pos = explode(":", $value["pos"]);
					$signPos = explode(":", $value["signPos"]);

					if (Server::getInstance()->loadLevel($value["levelName"]) && Server::getInstance()->loadLevel($value["signLevelName"])) {
						$level = StimoCommunity::getInstance()->getServer()->getLevelByName($value["levelName"]);
						$signLevel = StimoCommunity::getInstance()->getServer()->getLevelByName($value["signLevelName"]);

						$spawnPosition = new Position((int)$pos[0],(int)$pos[1],(int)$pos[2], $level);
						$signPosition = new Position((int)$signPos[0],(int)$signPos[1],(int)$signPos[2], $signLevel);

						StimoCommunity::$screenBoxes["{$signPosition->x}:{$signPosition->y}:{$signPosition->z}"] = new Screenbox($spawnPosition, $signPosition, $data["screenshotTime"]);
					}
				} catch (\Throwable $exception) {
					unset($data[$key]);
					$screenBoxFile->setAll($data);
				}
			}
			if ($screenBoxFile->hasChanged()) {
				$screenBoxFile->save();
			}
			$player->sendMessage(StimoCommunity::PREFIX . "§3§lDONE!");
		}
	}

	/**
	 * Function STAGE_SIGN
	 * @param BlockBreakEvent $event
	 * @return void
	 */
	public function STAGE_SIGN(BlockBreakEvent $event): void{
		if (self::$player == $event->getPlayer()->getName() && self::$stage == self::STAGE_SIGN) {
			if (!$event->getPlayer()->isSneaking()) {
				$sign = $event->getPlayer()->getLevel()->getTile($event->getBlock()->asVector3());
				if ($sign instanceof Sign) {
					self::$config["signLevelName"] = $sign->getLevel()->getFolderName();
					self::$config["signPos"] = "{$sign->x}:{$sign->y}:{$sign->z}";
					$this->nextStage($event->getPlayer());
					$event->setCancelled(true);
				} else {
					$event->getPlayer()->sendMessage(StimoCommunity::PREFIX . "Action cancelled.");
					$event->setCancelled(true);
				}
			}
		}
	}

	/**
	 * Function STAGE_SPAWN
	 * @param BlockBreakEvent $event
	 * @return void
	 */
	public function STAGE_SPAWN(BlockBreakEvent $event): void{
		if (self::$player == $event->getPlayer()->getName() && self::$stage == self::STAGE_SPAWN) {
			if (!$event->getPlayer()->isSneaking()) {
				self::$config["levelName"] = $event->getBlock()->getLevel()->getFolderName();
				self::$config["pos"] = "{$event->getBlock()->x}:{$event->getBlock()->y}:{$event->getBlock()->z}";
				$this->nextStage($event->getPlayer());
				$event->setCancelled(true);
			} else {
				$event->getPlayer()->sendMessage(StimoCommunity::PREFIX . "Action cancelled.");
				$event->setCancelled(true);
			}
		}
	}
}
