<?php
namespace xxAROX\StimoCommunity;
use pocketmine\entity\Entity;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\level\Position;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;
use pocketmine\utils\Config;
use pocketmine\utils\Utils;
use xxAROX\StimoCommunity\command\SetupCommand;
use xxAROX\StimoCommunity\entity\FireworksRocket;
use xxAROX\StimoCommunity\item\Firework;
use xxAROX\StimoCommunity\screenbox\Screenbox;
use xxAROX\StimoCommunity\screenbox\Setup;
use xxAROX\StimoCommunity\stage\Stage;


/**
 * Class StimoCommunity
 * @package xxAROX\StimoCommunity
 * @author xxAROX
 * @date 10.11.2020 - 04:05
 * @project StimoCommunity
 */
class StimoCommunity extends PluginBase{
	const PREFIX = "§dCommunity §8» §7";

	/** @var StimoCommunity */
	private static $instance;
	/** @var Stage */
	private static $stage;
	/** @var Screenbox[] */
	static $screenBoxes = [];


	/**
	 * Function onLoad
	 * @return void
	 */
	public function onLoad(){
		if (Utils::getOS() == Utils::OS_LINUX) {
			@mkdir("/home/");
			@mkdir("/home/.data/");
		}
		self::$instance = $this;
		foreach (array_diff(scandir($this->getFile() . "resources/songs/"), ["..","."]) as $fileName) {
			$this->saveResource("songs/" . $fileName);
		}
		$this->getLogger()->info("§eloaded");
	}

	/**
	 * Function onEnable
	 * @return void
	 */
	public function onEnable(){
		$this->getServer()->getPluginManager()->registerEvents(new Listener, $this);
		$this->getServer()->getPluginManager()->registerEvents(new Setup, $this);
		$this->getServer()->getCommandMap()->registerAll($this->getName(), [
			new SetupCommand("setup"),
		]);
		$this->registerItems();
		$this->registerEntities();

		$stageFile = (Utils::getOS() == Utils::OS_LINUX ? "/home/.data/stage.json" : $this->getDataFolder() . "stage.json");
		self::$stage = new Stage($stageFile, $this->getDataFolder() . "songs/");

		$screenBoxFile = new Config((Utils::getOS() == Utils::OS_LINUX ? "/home/.data/screenboxes.json" : $this->getDataFolder() . "screenboxes.json"));
		$data = $screenBoxFile->getAll();

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
				} else {
					throw new \Exception("Sign was removed.");
				}
			} catch (\Throwable $exception) {
				unset($data[$key]);
				$screenBoxFile->setAll($data);
			}
		}
		if ($screenBoxFile->hasChanged()) {
			$screenBoxFile->save();
		}
		$this->getLogger()->info("§aenabled");
	}

	/**
	 * Function onDisable
	 * @return void
	 */
	public function onDisable(){
		self::getStage()->getMusicPlayer()->setSongList([]);
		self::getStage()->getMusicPlayer()->clearPlayedSongQueue();
		$this->getLogger()->info("§cdisabled");
	}

	/**
	 * Function getInstance
	 * @return StimoCommunity
	 */
	static function getInstance(): StimoCommunity{
		return self::$instance;
	}

	/**
	 * Function getStage
	 * @return Stage
	 */
	static function getStage(): Stage{
		return self::$stage;
	}

	/**
	 * Function getScreenBoxes
	 * @return Screenbox[]
	 */
	static function getScreenBoxes(): array{
		return self::$screenBoxes;
	}

	/**
	 * Function registerItems
	 * @return void
	 */
	private function registerItems(): void{
		/** @var Item[] $array */
		$array = [
			new Firework(),
		];
		foreach ($array as $item) {
			ItemFactory::registerItem($item, true);
		}

		Item::initCreativeItems();
	}

	/**
	 * Function registerEntities
	 * @return void
	 */
	private function registerEntities(): void{
		Entity::registerEntity(FireworksRocket::class, true, ["minecraft:fireworks"]);
	}
}
