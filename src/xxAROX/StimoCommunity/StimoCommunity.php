<?php
namespace xxAROX\StimoCommunity;
use pocketmine\entity\Entity;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\utils\Utils;
use xxAROX\StimoCommunity\command\SetupCommand;
use xxAROX\StimoCommunity\entity\FireworksRocket;
use xxAROX\StimoCommunity\item\Firework;
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
	private static $stage;

	/**
	 * Function onLoad
	 * @return void
	 */
	public function onLoad(){
		self::$instance = $this;
	}

	/**
	 * Function onEnable
	 * @return void
	 */
	public function onEnable(){
		$this->getServer()->getPluginManager()->registerEvents(new Listener, $this);
		$this->getServer()->getCommandMap()->registerAll($this->getName(), [
			new SetupCommand("setup"),
		]);
		$this->registerItems();
		$this->registerEntities();
		self::$stage = new Stage((Utils::getOS() == Utils::OS_LINUX ? "/home/.data/stage.json" : StimoCommunity::getInstance()->getDataFolder() . "stage.json"));
	}

	/**
	 * Function onDisable
	 * @return void
	 */
	public function onDisable(){
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
	public static function getStage(): Stage{
		return self::$stage;
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
