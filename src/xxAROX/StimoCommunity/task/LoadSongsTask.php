<?php
namespace xxAROX\StimoCommunity\task;
use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;
use xenialdan\libnbs\NBSFile;
use xenialdan\libnbs\Song;
use xxAROX\StimoCommunity\StimoCommunity;


/**
 * Class LoadSongsTask
 * @package xxAROX\StimoCommunity\task
 * @author xxAROX
 * @date 10.11.2020 - 10:24
 * @project StimoCommunity
 */
class LoadSongsTask extends AsyncTask{
	/** @var string */
	private $songPath;

	/**
	 * LoadSongsTask constructor.
	 * @param string $songPath
	 */
	public function __construct(string $songPath){
		$this->songPath = $songPath;
	}

	/**
	 * Function onRun
	 * @return void
	 */
	public function onRun(){
		$list = $errors = [];
		foreach (glob($this->songPath . DIRECTORY_SEPARATOR . "*.nbs") as $path) {
			try {
				$song = NBSFile::parse($path);
				if ($song !== null) $list[] = $song;
			} catch (\Exception $e) {
				$errors[] = "This song could not be read: " . basename($path, ".nbs");
				$errors[] = $e->getMessage();
				$errors[] = $e->getTraceAsString();
			}
		}
		$this->setResult(compact("list", "errors"));
	}

	/**
	 * Function onCompletion
	 * @param Server $server
	 * @return void
	 */
	public function onCompletion(Server $server){
		$result = $this->getResult();
		/**
		 * @var Song[] $songlist
		 * @var string[] $errors
		 */
		[$songlist, $errors] = [$result["list"], $result["errors"]];
		$server->getLogger()->info("Loaded " . count($songlist) . " songs");
		$songlist = array_values($songlist);
		StimoCommunity::getStage()->getMusicPlayer()->setSongList($songlist);

		foreach ($errors as $i => $error) {
			$server->getLogger()->error($error);
			if ($i > 5) break;
			next($songlist);
		}
	}
}