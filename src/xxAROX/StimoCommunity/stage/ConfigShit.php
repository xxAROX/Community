<?php
namespace xxAROX\StimoCommunity\stage;
use pocketmine\math\Vector3;
use pocketmine\utils\Config;


/**
 * Class ConfigShit
 * @package xxAROX\StimoCommunity\stage
 * @author xxAROX
 * @date 10.11.2020 - 07:26
 * @project StimoCommunity
 */
class ConfigShit{
	/** @var Config */
	protected $file;
	/** @var array */
	protected $fireworkPositions = [];
	/** @var array */
	protected $vulkanPositions = [];


	/**
	 * ConfigShit constructor.
	 * @param string $path
	 */
	public function __construct(string $path){
		$this->file = new Config($path, Config::JSON);

		if (!$this->file->exists("firework-positions")) {
			$this->file->set("firework-positions", []);
		}
		if (!$this->file->exists("vulkan-positions")) {
			$this->file->set("vulkan-positions", []);
		}
		if ($this->file->hasChanged()) {
			$this->file->save();
		}
	}

	/**
	 * Function addFireworkPosition
	 * @param Vector3 $vector3
	 * @return void
	 */
	public function addFireworkPosition(Vector3 $vector3): void{
		$this->fireworkPositions["{$vector3->x}:{$vector3->y}:{$vector3->z}"] = $vector3;
		$this->file->set("firework-positions", array_keys($this->fireworkPositions));
		$this->file->save();
	}

	/**
	 * Function isFireworkPosition
	 * @param Vector3 $vector3
	 * @return bool
	 */
	public function isFireworkPosition(Vector3 $vector3): bool{
		return isset($this->fireworkPositions["{$vector3->x}:{$vector3->y}:{$vector3->z}"]);
	}

	/**
	 * Function addFireworkPosition
	 * @param Vector3 $vector3
	 * @return void
	 */
	public function removeFireworkPosition(Vector3 $vector3): void{
		unset($this->fireworkPositions["{$vector3->x}:{$vector3->y}:{$vector3->z}"]);
		$this->file->set("firework-positions", array_keys($this->fireworkPositions));
		$this->file->save();
	}

	/**
	 * Function addVulkanPosition
	 * @param Vector3 $vector3
	 * @return void
	 */
	public function addVulkanPosition(Vector3 $vector3): void{
		$this->vulkanPositions["{$vector3->x}:{$vector3->y}:{$vector3->z}"] = $vector3;
		$this->file->set("vulkan-positions", array_keys($this->vulkanPositions));
		$this->file->save();
	}

	/**
	 * Function isVulkanPosition
	 * @param Vector3 $vector3
	 * @return bool
	 */
	public function isVulkanPosition(Vector3 $vector3): bool{
		return isset($this->vulkanPositions["{$vector3->x}:{$vector3->y}:{$vector3->z}"]);
	}

	/**
	 * Function addVulkanPosition
	 * @param Vector3 $vector3
	 * @return void
	 */
	public function removeVulkanPosition(Vector3 $vector3): void{
		unset($this->vulkanPositions["{$vector3->x}:{$vector3->y}:{$vector3->z}"]);
		$this->file->set("vulkan-positions", array_keys($this->vulkanPositions));
		$this->file->save();
	}
}
