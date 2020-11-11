<?php
namespace xxAROX\StimoCommunity\task;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;
use pocketmine\Player;
use pocketmine\scheduler\Task;
use pocketmine\Server;
use xenialdan\libnbs\Layer;
use xenialdan\libnbs\NBSFile;
use xenialdan\libnbs\Song;
use xxAROX\StimoCommunity\StimoCommunity;


/**
 * Class PlaySongTask
 * @package xxAROX\StimoCommunity\task
 * @author xxAROX
 * @date 10.11.2020 - 10:37
 * @project StimoCommunity
 */
class PlaySongTask extends Task{
	/** @var Song */
	public $song = null;
	/** @var string */
	public $songfilename = "";
	/** @var bool */
	protected $playing = false;
	/** @var int */
	private $tick = -1;
	public $pause = false;


	/**
	 * PlaySongTask constructor.
	 * @param string $songfilename
	 * @param Song $song
	 */
	public function __construct(string $songfilename, Song $song){
		$this->song = $song;
		$this->songfilename = $songfilename;
		$this->playing = true;
		#$owner->getServer()->broadcastMessage(TextFormat::GREEN . $this->owner->getDescription()->getPrefix() . " Now playing: " . (empty($this->song->getTitle()) ? basename($songfilename, ".nbs") : $this->song->getTitle()) . (empty($this->song->getAuthor()) ? "" : " by " . $this->song->getAuthor()));
	}

	/**
	 * Function onRun
	 * @param int $currentTick
	 * @return void
	 */
	public function onRun(int $currentTick){
		if (!$this->pause) {
			if (!$this->playing) {
				return;
			}
			if ($this->tick > $this->song->getLength()) {
				$this->tick = -1;
				$this->playing = false;
				if (StimoCommunity::getStage()->getMusicPlayer()->isLooping()) {
					$song = $this->song;
				} else {
					$song = StimoCommunity::getStage()->getMusicPlayer()->getNextSong();
				}
				StimoCommunity::getStage()->getMusicPlayer()->playNext($song);
				return;
			}
			$this->tick++;

			foreach (Server::getInstance()->getOnlinePlayers() as $player) {
				$this->playTick($player, $this->tick);
			}
		}
	}

	/**
	 * Function playTick
	 * @param Player $player
	 * @param int $tick
	 * @return void
	 */
	private function playTick(Player $player, int $tick): void{
		/** @var Layer $layer */
		/** @noinspection PhpUndefinedMethodInspection */
		foreach ($this->song->getLayerHashMap()->values()->toArray() as $layer) {
			$note = $layer->getNote($tick);
			if ($note === null) {
				continue;
			}
			$pk = new PlaySoundPacket();
			$pk->soundName = NBSFile::MAPPING[$note->instrument] ?? NBSFile::MAPPING[NBSFile::INSTRUMENT_PIANO];
			$pk->pitch = 2 ** (($note->getKey() - 45) / 12);
			$pk->volume = ($layer->getVolume() * StimoCommunity::getStage()->getMusicPlayer()->getVolume()) / 10000;
			$vector = $player->asVector3();
			/*if ($layer->stereo !== 100) {//Not centered, modify position. TODO fix
				$yaw = ($player->yaw - 90) % 360;
				$add = (new Vector2(-cos(deg2rad($yaw) - M_PI_2), -sin(deg2rad($yaw) - M_PI_2)))->normalize();
				$multiplier = 2 * (($layer->stereo - 100) / 100);
				$add = $add->multiply($multiplier);
				$vector->add($add->x, 0, $add->y);
			}*/
			$pk->x = $vector->x;
			$pk->y = $vector->y + $player->getEyeHeight();
			$pk->z = $vector->z;
			$player->dataPacket($pk);
			unset($add, $pk, $vector, $note);
		}
	}
}
