<?php
namespace xxAROX\StimoCommunity\stage;
use pocketmine\level\Level;
use pocketmine\level\particle\GenericParticle;
use pocketmine\level\particle\LavaParticle;
use pocketmine\level\Position;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\BlockEventPacket;
use pocketmine\Player;
use pocketmine\scheduler\ClosureTask;
use pocketmine\Server;
use xxAROX\StimoCommunity\entity\FireworksRocket;
use xxAROX\StimoCommunity\item\Firework;
use xxAROX\StimoCommunity\StimoCommunity;


/**
 * Class Stage
 * @package xxAROX\StimoCommunity\stage
 * @author xxAROX
 * @date 10.11.2020 - 04:10
 * @project StimoCommunity
 */
class Stage extends ConfigShit{
	/** @var MusicPlayer */
	private $musicPlayer;
	/** @var Level */
	private $level;
	/** @var Vector3 */
	private $pos1;
	/** @var Vector3 */
	private $pos2;

	/** @var int */
	public $fireworkDelay = 20;
	/** @var int */
	public $fireworkSettings = [
		"type"    => Firework::TYPE_BURST,
		"color"   => "random",
		"fade"    => false,
		"flicker" => false,
	];
	/** @var bool */
	public $fireworksEnabled = false;
	/** @var bool */
	public $vulkanEnabled = false;


	/**
	 * Stage constructor.
	 * @param string $path
	 * @param string $musicPath
	 */
	public function __construct(string $path, string $musicPath){
		parent::__construct($path);
		$this->musicPlayer = new MusicPlayer($musicPath);

		if (
			is_null($this->file->get("levelName", null))
			OR is_null($this->file->get("pos1", null))
			OR is_null($this->file->get("pos2", null))
		) {
			return;
		}
		$pos1 = explode(":", $this->file->get("pos1"));
		$pos2 = explode(":", $this->file->get("pos2"));

		$this->level = Server::getInstance()->getLevelByName($this->file->get("levelName"));
		$this->pos1 = new Vector3((int)$pos1[0],(int)$pos1[1],(int)$pos1[2]);
		$this->pos2 = new Vector3((int)$pos2[0],(int)$pos2[1],(int)$pos2[2]);

		$this->load();
	}

	/**
	 * Function getMusicPlayer
	 * @return MusicPlayer
	 */
	public function getMusicPlayer(): MusicPlayer{
		return $this->musicPlayer;
	}

	/**
	 * Function isOnStage
	 * @param Player $player
	 * @return bool
	 */
	public function isOnStage(Player $player): bool{
		$cord1 = $this->pos1;
		$cord2 = $this->pos2;

		$minx = min($cord1->getX() +0.5, $cord2->getX() +0.5);
		$miny = min($cord1->getY(), $cord2->getY());
		$minz = min($cord1->getZ() +0.5, $cord2->getZ() +0.5);
		$maxx = max($cord1->getX() +0.5, $cord2->getX() +0.5);
		$maxy = max($cord1->getY(), $cord2->getY());
		$maxz = max($cord1->getZ() +0.5, $cord2->getZ() +0.5);
		$bb = new AxisAlignedBB($minx, $miny, $minz, $maxx, $maxy, $maxz);

		if ($bb->isVectorInside($player)) {
			return true;
		}
		return false;
	}

	/**
	 * Function setPos1
	 * @param Position $pos1
	 * @return void
	 */
	public function setPos1(Position $pos1): void{
		$this->level = $pos1->getLevel();
		$this->pos1 = $pos1->asVector3();

		$this->file->set("levelName", $pos1->getLevel()->getFolderName());
		$this->file->set("pos1", "{$pos1->x}:{$pos1->y}:{$pos1->z}");
		$this->file->save();
	}

	/**
	 * Function setPos2
	 * @param Position $pos2
	 * @return void
	 */
	public function setPos2(Position $pos2): void{
		$this->level = $pos2->getLevel();
		$this->pos2 = $pos2->asVector3();

		$this->file->set("levelName", $pos2->getLevel()->getFolderName());
		$this->file->set("pos2", "{$pos2->x}:{$pos2->y}:{$pos2->z}");
		$this->file->save();
	}

	/**
	 * Function load
	 * @return void
	 */
	protected function load(): void{
		parent::load();
		$this->loadEntities();
		StimoCommunity::getInstance()->getScheduler()->scheduleRepeatingTask(new ClosureTask(
			function (int $currentTick): void{
				$this->tick();
				foreach (StimoCommunity::getScreenBoxes() as $strPos => $screenBox) {
					$screenBox->tick();
				}
			}), 1);
	}

	/**
	 * Function loadEntities
	 * @return void
	 */
	protected function loadEntities(): void{
		#TODO
	}

	/**
	 * Function tick
	 * @return void
	 */
	private function tick(): void{
		if ($this->fireworksEnabled && Server::getInstance()->getTick() %$this->fireworkDelay == 0) {
			$this->spawnFireworks($this->fireworkSettings["type"], $this->fireworkSettings["color"]);
		}
		if ($this->vulkanEnabled) {
			$this->spawnVolcanoes();
		}
		if ($this->musicPlayer->isPlaying() && Server::getInstance()->getTick() %4 == 0) {
			$this->activateMusicBoxes();
		}
	}

	/**
	 * Function spawnFireworks
	 * @param int $type
	 * @param string $color
	 * @return void
	 */
	public function spawnFireworks(int $type = Firework::TYPE_SMALL_SPHERE, string $color = "random"): void{
		$colors = ["\x00","\x01","\x02","\x03","\x04","\x05","\x06","\x07","\x08","\x09","\x0a","\x0b","\x0c","\x0d","\x0e","\x0f"];
		foreach ($this->fireworkPositions as $fireworkPosition) {
			FireworksRocket::spawn(new Position($fireworkPosition->x +0.5, $fireworkPosition->y, $fireworkPosition->z +0.5, $this->level), $type, ($color === "random" ? ($colors[mt_rand(0,count($colors) -1)]) : $color), $this->fireworkSettings["fade"], $this->fireworkSettings["flicker"]);
		}
	}

	/**
	 * Function spawnVolcanoes
	 * @return void
	 */
	public function spawnVolcanoes(): void{
		foreach ($this->vulkanPositions as $vulkanPosition) {
			$radius = 0.2;
			$this->level->addParticle(new LavaParticle(new Vector3($vulkanPosition->x +0.5 +($radius *cos(deg2rad(0))), $vulkanPosition->y +1.2, $vulkanPosition->z +0.5 +($radius *sin(deg2rad(0))))));
			$this->level->addParticle(new LavaParticle(new Vector3($vulkanPosition->x +0.5 +($radius *cos(deg2rad(90))), $vulkanPosition->y +1.2, $vulkanPosition->z +0.5 +($radius *sin(deg2rad(90))))));
			$this->level->addParticle(new LavaParticle(new Vector3($vulkanPosition->x +0.5 +($radius *cos(deg2rad(180))), $vulkanPosition->y +1.2, $vulkanPosition->z +0.5 +($radius *sin(deg2rad(180))))));
			$this->level->addParticle(new LavaParticle(new Vector3($vulkanPosition->x +0.5 +($radius *cos(deg2rad(270))), $vulkanPosition->y +1.2, $vulkanPosition->z +0.5 +($radius *sin(deg2rad(270))))));
		}
	}

	/**
	 * Function spawnVolcanoes
	 * @return void
	 */
	public function activateMusicBoxes(): void{
		foreach ($this->musicBoxPositions as $vulkanPosition) {$pk = new BlockEventPacket();
			$radius = 0.5;
			$this->level->addParticle(new GenericParticle(new Vector3($vulkanPosition->x +0.5 +($radius *cos(deg2rad(0))), $vulkanPosition->y +0.5, $vulkanPosition->z +0.5 +($radius *sin(deg2rad(0)))), GenericParticle::TYPE_NOTE, mt_rand(0,15)));
			$this->level->addParticle(new GenericParticle(new Vector3($vulkanPosition->x +0.5 +($radius *cos(deg2rad(90))), $vulkanPosition->y +0.5, $vulkanPosition->z +0.5 +($radius *sin(deg2rad(90)))), GenericParticle::TYPE_NOTE, mt_rand(0,15)));
			$this->level->addParticle(new GenericParticle(new Vector3($vulkanPosition->x +0.5 +($radius *cos(deg2rad(180))), $vulkanPosition->y +0.5, $vulkanPosition->z +0.5 +($radius *sin(deg2rad(180)))), GenericParticle::TYPE_NOTE, mt_rand(0,15)));
			$this->level->addParticle(new GenericParticle(new Vector3($vulkanPosition->x +0.5 +($radius *cos(deg2rad(270))), $vulkanPosition->y +0.5, $vulkanPosition->z +0.5 +($radius *sin(deg2rad(270)))), GenericParticle::TYPE_NOTE, mt_rand(0,15)));
		}
	}
}
