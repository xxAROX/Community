<?php
namespace xxAROX\StimoCommunity\stage;
use pocketmine\Server;
use xenialdan\libnbs\Song;
use xxAROX\StimoCommunity\StimoCommunity;
use xxAROX\StimoCommunity\task\LoadSongsTask;
use xxAROX\StimoCommunity\task\PlaySongTask;


/**
 * Class MusicPlayer
 * @package xxAROX\StimoCommunity\stage
 * @author xxAROX
 * @date 10.11.2020 - 10:28
 * @project StimoCommunity
 */
class MusicPlayer{
	/** @var null|PlaySongTask */
	private $activeSong = null;
	/** @var Song[] */
	private $songQueue = [];
	/** @var Song[] */
	private $songList = [];
	/** @var int */
	private $volume = 10;
	/** @var bool */
	private $looping = false;

	/**
	 * MusicPlayer constructor.
	 * @param string $songArchivePath
	 */
	public function __construct(string $songArchivePath){
		Server::getInstance()->getAsyncPool()->submitTask(new LoadSongsTask($songArchivePath));
	}

	/**
	 * Function setVolume
	 * @param int $volume
	 * @return void
	 */
	public function setVolume(int $volume): void{
		$this->volume = $volume;
	}

	/**
	 * Function getVolume
	 * @return int
	 */
	public function getVolume(): int{
		return $this->volume;
	}

	/**
	 * Function setSongList
	 * @param Song[] $songList
	 * @return void
	 */
	public function setSongList(array $songList): void{
		$this->songList = $songList;
	}

	/**
	 * Function getSongList
	 * @return array
	 */
	public function getSongList(): array{
		return $this->songList;
	}

	/**
	 * Function getRandomSong
	 * @return null|Song
	 */
	public function getRandomSong(): ?Song{
		if (empty($this->songList)) {
			return null;
		}
		return $this->songList[array_rand($this->songList)];
	}

	/**
	 * Function getNextSong
	 * @return null|Song
	 */
	public function getNextSong(): ?Song{
		$song = next($this->songList);
		if ($song === false) {
			$song = reset($this->songList);
		}
		if ($song === false) {
			return null;
		}
		return $song;
	}

	/**
	 * Function pause
	 * @return void
	 */
	public function pause(): void{
		if ($this->activeSong instanceof PlaySongTask) {
			if (!$this->activeSong->pause) {
				$this->activeSong->pause = true;
			}
		}
	}

	/**
	 * Function resume
	 * @return void
	 */
	public function resume(): void{
		if ($this->activeSong instanceof PlaySongTask) {
			if ($this->activeSong->pause) {
				$this->activeSong->pause = false;
			}
		}
	}

	/**
	 * Function getActiveSong
	 * @return null|Song
	 */
	public function getActiveSong(): ?Song{
		if ($this->activeSong instanceof PlaySongTask) {
			return $this->activeSong->song;
		}
		return null;
	}

	/**
	 * Function playNext
	 * @param null|Song $song
	 * @return void
	 */
	public function playNext(?Song $song = null){
		$this->stop();
		$this->startTask($song);
	}

	/**
	 * Function stop
	 * @param null|bool $store
	 * @return void
	 */
	public function stop(?bool $store = true){
		if ($this->activeSong instanceof PlaySongTask) {
			if ($store && $this->activeSong->song instanceof Song) {
				$this->songQueue = array_values($this->songQueue);
				$this->songQueue[count($this->songQueue)] = $this->activeSong->song;
			}
			StimoCommunity::getInstance()->getScheduler()->cancelTask($this->activeSong->getTaskId());
			$this->activeSong = null;
		}
	}

	/**
	 * Function toggleLoop
	 * @return void
	 */
	public function toggleLoop(): void{
		$this->looping = !$this->looping;
	}

	/**
	 * Function getLooping
	 * @return bool
	 */
	public function isLooping(): bool{
		return $this->looping;
	}

	/**
	 * Function before
	 * @return void
	 */
	public function before(){
		$this->stop(false);
		if (isset($this->songQueue[count($this->songQueue) -1])) {
			$this->playNext($this->songQueue[count($this->songQueue) -1]);
			unset($this->songQueue[count($this->songQueue) -1]);
		}
	}

	/**
	 * Function skip
	 * @return void
	 */
	public function skip(){
		$this->stop();
		$this->playNext($this->getNextSong());
	}

	/**
	 * Function clearPlayedSongQueue
	 * @return void
	 */
	public function clearPlayedSongQueue(): void{
		$this->songQueue = [];
	}

	/**
	 * Function isPause
	 * @return bool
	 */
	public function isPause(): bool{
		if ($this->activeSong instanceof PlaySongTask) {
			return $this->activeSong->pause;
		}
		return true;
	}

	/**
	 * Function isPlaying
	 * @return bool
	 */
	public function isPlaying(): bool{
		return $this->activeSong instanceof PlaySongTask;
	}

	/**
	 * Function startTask
	 * @param null|Song $song
	 * @return void
	 */
	public function startTask(?Song $song = null): void{
		$song = $song ?? self::getNextSong();
		if (!$song instanceof Song) {
			#$this->getLogger()->warning("Could not start radio: No music found / given");
			return;
		}
		$this->activeSong = new PlaySongTask($song->getPath(), $song);
		StimoCommunity::getInstance()->getScheduler()->scheduleDelayedRepeatingTask($this->activeSong, 20 * 3, intval(floor($song->getDelay())));
	}
}
