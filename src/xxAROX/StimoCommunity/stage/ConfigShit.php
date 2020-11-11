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
	/** @var array */
	protected $musicDeskPositions = [];
	/** @var array */
	protected $settingsPositions = [];
	/** @var array */
	protected $musicBoxPositions = [];


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
		if (!$this->file->exists("music-desk-positions")) {
			$this->file->set("music-desk-positions", []);
		}
		if (!$this->file->exists("settings-positions")) {
			$this->file->set("settings-positions", []);
		}
		if (!$this->file->exists("music-box-positions")) {
			$this->file->set("music-box-positions", []);
		}
		if ($this->file->hasChanged()) {
			$this->file->save();
		}
	}
	/**
	 * Function load
	 * @return void
	 */
	protected function load(): void{
		$this->fireworkPositions = [];
		foreach ($this->file->get("firework-positions", []) as $strPos) {
			$pos = explode(":", $strPos);
			$this->fireworkPositions[$strPos] = new Vector3((int)$pos[0],(int)$pos[1],(int)$pos[2]);
		}

		$this->vulkanPositions = [];
		foreach ($this->file->get("vulkan-positions", []) as $strPos) {
			$pos = explode(":", $strPos);
			$this->vulkanPositions[$strPos] = new Vector3((int)$pos[0],(int)$pos[1],(int)$pos[2]);
		}

		$this->musicDeskPositions = [];
		foreach ($this->file->get("music-desk-positions", []) as $strPos) {
			$pos = explode(":", $strPos);
			$this->musicDeskPositions[$strPos] = new Vector3((int)$pos[0],(int)$pos[1],(int)$pos[2]);
		}

		$this->settingsPositions = [];
		foreach ($this->file->get("settings-positions", []) as $strPos) {
			$pos = explode(":", $strPos);
			$this->settingsPositions[$strPos] = new Vector3((int)$pos[0],(int)$pos[1],(int)$pos[2]);
		}

		$this->musicBoxPositions = [];
		foreach ($this->file->get("music-box-positions", []) as $strPos) {
			$pos = explode(":", $strPos);
			$this->musicBoxPositions[$strPos] = new Vector3((int)$pos[0],(int)$pos[1],(int)$pos[2]);
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


	/**
	 * Function addMusicDeskPosition
	 * @param Vector3 $vector3
	 * @return void
	 */
	public function addMusicDeskPosition(Vector3 $vector3): void{
		$this->musicDeskPositions["{$vector3->x}:{$vector3->y}:{$vector3->z}"] = $vector3;
		$this->file->set("music-desk-positions", array_keys($this->musicDeskPositions));
		$this->file->save();
	}
	/**
	 * Function isMusicDeskPosition
	 * @param Vector3 $vector3
	 * @return bool
	 */
	public function isMusicDeskPosition(Vector3 $vector3): bool{
		return isset($this->musicDeskPositions["{$vector3->x}:{$vector3->y}:{$vector3->z}"]);
	}
	/**
	 * Function addMusicDeskPosition
	 * @param Vector3 $vector3
	 * @return void
	 */
	public function removeMusicDeskPosition(Vector3 $vector3): void{
		unset($this->musicDeskPositions["{$vector3->x}:{$vector3->y}:{$vector3->z}"]);
		$this->file->set("music-desk-positions", array_keys($this->musicDeskPositions));
		$this->file->save();
	}


	/**
	 * Function addSettingsPosition
	 * @param Vector3 $vector3
	 * @return void
	 */
	public function addSettingsPosition(Vector3 $vector3): void{
		$this->settingsPositions["{$vector3->x}:{$vector3->y}:{$vector3->z}"] = $vector3;
		$this->file->set("settings-positions", array_keys($this->settingsPositions));
		$this->file->save();
	}
	/**
	 * Function isSettingsPosition
	 * @param Vector3 $vector3
	 * @return bool
	 */
	public function isSettingsPosition(Vector3 $vector3): bool{
		return isset($this->settingsPositions["{$vector3->x}:{$vector3->y}:{$vector3->z}"]);
	}
	/**
	 * Function removeSettingsPosition
	 * @param Vector3 $vector3
	 * @return void
	 */
	public function removeSettingsPosition(Vector3 $vector3): void{
		unset($this->settingsPositions["{$vector3->x}:{$vector3->y}:{$vector3->z}"]);
		$this->file->set("settings-positions", array_keys($this->settingsPositions));
		$this->file->save();
	}


	/**
	 * Function addMusicDeskPosition
	 * @param Vector3 $vector3
	 * @return void
	 */
	public function addMusicBoxPosition(Vector3 $vector3): void{
		$this->musicBoxPositions["{$vector3->x}:{$vector3->y}:{$vector3->z}"] = $vector3;
		$this->file->set("music-box-positions", array_keys($this->musicBoxPositions));
		$this->file->save();
	}
	/**
	 * Function isMusicDeskPosition
	 * @param Vector3 $vector3
	 * @return bool
	 */
	public function isMusicBoxPosition(Vector3 $vector3): bool{
		return isset($this->musicBoxPositions["{$vector3->x}:{$vector3->y}:{$vector3->z}"]);
	}
	/**
	 * Function addMusicDeskPosition
	 * @param Vector3 $vector3
	 * @return void
	 */
	public function removeMusicBoxPosition(Vector3 $vector3): void{
		unset($this->musicBoxPositions["{$vector3->x}:{$vector3->y}:{$vector3->z}"]);
		$this->file->set("music-box-positions", array_keys($this->musicBoxPositions));
		$this->file->save();
	}
}
