<?php
namespace xxAROX\StimoCommunity\stage;
use pocketmine\level\Level;
use pocketmine\level\Position;
use pocketmine\math\Vector3;
use pocketmine\Server;
use pocketmine\utils\Config;
use pocketmine\utils\Utils;
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
class Stage{
	/** @var Level */
	private $level;
	/** @var Vector3 */
	private $pos1;
	/** @var Vector3 */
	private $pos2;
	/** @var Config */
	private $file;

	/** @var Vector3[] */
	private $fireworkPositions = [];


	/**
	 * Stage constructor.
	 * @param string $path
	 */
	public function __construct(string $path){
		$this->file = new Config($path, Config::JSON);

		if (!$this->file->exists("levelName")) {
			$this->file->set("levelName", null);
		}
		if (!$this->file->exists("pos1")) {
			$this->file->set("pos1", null);
		}
		if (!$this->file->exists("pos2")) {
			$this->file->set("pos2", null);
		}
		if ($this->file->hasChanged()) {
			$this->file->save();
			#throw new \Exception("Please configure this shit");
		}

		if (
			is_null($this->file->get("levelName"))
			OR is_null($this->file->get("pos1"))
			OR is_null($this->file->get("pos2"))
		) {
			return;
		}

		$this->level = Server::getInstance()->getLevelByName($this->file->get("levelName"));
		$this->pos1 = new Vector3(...explode(":", $this->file->get("pos1")));
		$this->pos2 = new Vector3(...explode(":", $this->file->get("pos2")));

		$this->load();
	}

	/**
	 * Function load
	 * @return void
	 */
	protected function load(): void{
		$this->fireworkPositions = [];
		foreach ($this->file->get("firework-positions", []) as $strPos) {
			$this->fireworkPositions[] = new Vector3(...explode(":", $strPos));
		}

		$this->loadEntities();
	}

	/**
	 * Function loadEntities
	 * @return void
	 */
	protected function loadEntities(): void{
		#TODO
	}

	/**
	 * Function addFireworkPosition
	 * @param Vector3 $vector3
	 * @return void
	 */
	public function addFireworkPosition(Vector3 $vector3): void{
		$this->file->set("firework-positions", ["{$vector3->x}:{$vector3->y}:{$vector3->z}"]);
		$this->file->save();
	}

	/**
	 * Function addFireworkPosition
	 * @param Vector3 $vector3
	 * @return void
	 */
	public function removeFireworkPosition(Vector3 $vector3): void{
		$this->file->removeNested("firework-positions.{$vector3->x}:{$vector3->y}:{$vector3->z}");
		$this->file->save();
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
			FireworksRocket::spawn(new Position($fireworkPosition->x, $fireworkPosition->y, $fireworkPosition->z, $this->level), $type, ($color === "random" ? ($colors[mt_rand(0,count($colors) -1)]) : $color), true);
		}
	}
}
