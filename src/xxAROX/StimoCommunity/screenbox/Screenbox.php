<?php
namespace xxAROX\StimoCommunity\screenbox;
use pocketmine\block\SignPost;
use pocketmine\block\WallSign;
use pocketmine\level\Position;
use pocketmine\math\Vector3;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\tile\Sign;
use xxAROX\StimoCommunity\StimoCommunity;


/**
 * Class Screenbox
 * @package xxAROX\StimoCommunity\screenbox
 * @author xxAROX
 * @date 11.11.2020 - 03:58
 * @project StimoCommunity
 */
class Screenbox{
	/** @var Position */
	private $spawnPosition;
	/** @var Position */
	private $joinSignPosition;
	/** @var null|Player */
	private $youTuber = null;
	/** @var null|Vector3 */
	private $youTuberPositionBefore = null;
	/** @var null|Player */
	private $player = null;
	/** @var null|Vector3 */
	private $playerPositionBefore = null;
	/** @var int */
	private $countDown = 30;
	/** @var int */
	private $screenshotTime;
	/** @var string[] */
	private $queue = [];
	/** @var bool */
	private $loadingValue = false;


	/**
	 * Screenbox constructor.
	 * @param Position $spawnPosition
	 * @param Position $joinSignPosition
	 * @param int $screenshotTime
	 */
	public function __construct(Position $spawnPosition, Position $joinSignPosition, int $screenshotTime = 30){
		$this->spawnPosition = $spawnPosition;
		$this->joinSignPosition = $joinSignPosition;
		$this->screenshotTime = $screenshotTime;
	}

	/**
	 * Function getSpawnPosition
	 * @return Position
	 */
	public function getSpawnPosition(): Position{
		return $this->spawnPosition;
	}

	/**
	 * Function getJoinSignPosition
	 * @return Position
	 */
	public function getJoinSignPosition(): Position{
		return $this->joinSignPosition;
	}

	/**
	 * Function getSign
	 * @return null|Sign
	 */
	public function getSign(): ?Sign{
		$tile = $this->joinSignPosition->getLevel()->getTile($this->joinSignPosition->asVector3());
		return ($tile instanceof Sign ? $tile : null);
	}

	/**
	 * Function countdown
	 * @return void
	 */
	public function countdown(): void{
		$this->countDown--;
		if ($this->countDown == 0) {
			$this->countDown = $this->screenshotTime;
			$this->clearBox(false);
			return;
		}
	}

	/**
	 * Function clearBox
	 * @param bool $youtuber
	 * @return void
	 */
	public function clearBox(bool $youtuber = true): void{
		if ($youtuber) {
			if (!is_null($this->youTuber)) {
				$this->youTuber->teleport($this->youTuberPositionBefore);
				$this->youTuber = null;
				if (!is_null($this->player)) {
					$this->player->sendMessage(StimoCommunity::PREFIX . $this->youTuber->getDisplayName() . "§7 left");
				}
			}
		}
		if (!is_null($this->player)) {
			$this->player->teleport($this->playerPositionBefore);
			$this->player = null;
			while (!Server::getInstance()->getPlayer($this->queue[0]) instanceof Player) {
				unset($this->queue[0]);
				$this->queue = array_values($this->queue);
			}
			$this->setPlayer(Server::getInstance()->getPlayer($this->queue[0]));
		}
	}

	/**
	 * Function isInQueue
	 * @param Player $player
	 * @return bool
	 */
	public function isInQueue(Player $player): bool{
		return in_array($player->getName(), $this->queue);
	}

	/**
	 * Function addToQueue
	 * @param Player $player
	 * @return void
	 */
	public function addToQueue(Player $player): void{
		if (!isset($this->queue[$player->getName()])) {
			$this->queue[] = $player->getName();
		}
	}

	/**
	 * Function removeFromQueue
	 * @param Player $player
	 * @return void
	 */
	public function removeFromQueue(Player $player): void{
		if (isset($this->queue[$player->getName()])) {
			unset($this->queue[$player->getName()]);
			$this->queue = array_values($this->queue);
		}
	}

	/**
	 * Function getYouTuber
	 * @return null|Player
	 */
	public function getYouTuber(): ?Player{
		return $this->youTuber;
	}

	/**
	 * Function setYouTuber
	 * @param null|Player $youTuber
	 * @return void
	 */
	public function setYouTuber(?Player $youTuber): void{
		$this->youTuber = $youTuber;
		$this->youTuberPositionBefore = $youTuber->asVector3();
		$youTuber->teleport($this->getSpawnPosition());
	}

	/**
	 * Function getPlayer
	 * @return null|Player
	 */
	public function getPlayer(): ?Player{
		return $this->player;
	}

	/**
	 * Function setPlayer
	 * @param null|Player $player
	 * @return void
	 */
	public function setPlayer(?Player $player): void{
		$this->player = $player;
		$this->playerPositionBefore = $player->asVector3();
		$player->teleport($this->getSpawnPosition());
	}

	/**
	 * Function tick
	 * @return void
	 */
	public function tick(): void{
		if (Server::getInstance()->getTick() %20 == 0) {
			if ($this->getPlayer() instanceof Player) {
				$this->countdown();
			}
			$this->updateSign();
		}
	}

	/**
	 * Function updateSign
	 * @return void
	 */
	private function updateSign(): void{
		$block = $this->joinSignPosition->getLevel()->getBlock($this->joinSignPosition->asVector3());
		if (!is_null($this->getSign()) && ($block instanceof SignPost || $block instanceof WallSign)) {
			if ($this->getYouTuber() instanceof Player) {
				if ($this->getPlayer() instanceof Player) {
					$this->getSign()->setText("§f------------", $this->getYouTuber()->getDisplayName(), "§e{$this->countDown} left", "§f------------");
				} else {
					$this->getSign()->setText("§f------------", $this->getYouTuber()->getDisplayName(), "§3waiting", "§f------------");
				}
			} else {
				$line = ($this->loadingValue ? "§f- - - - - - - -" : "§7- - - - - - - -");
				$empty = ($this->loadingValue ? "§9Empty" : "§1Empty");
				$screenbox = ($this->loadingValue ? "§9Screenbox" : "§1Screenbox");
				$this->loadingValue = !$this->loadingValue;
				$this->getSign()->setText("{$line}", "{$empty}", "{$screenbox}", "{$line}");
			}
		} else {
			$this->joinSignPosition->getLevel()->setBlock($this->joinSignPosition->asVector3(), new WallSign(0));
		}
	}
}
