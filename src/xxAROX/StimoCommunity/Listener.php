<?php
namespace xxAROX\StimoCommunity;
use Frago9876543210\EasyForms\elements\Button;
use Frago9876543210\EasyForms\elements\Dropdown;
use Frago9876543210\EasyForms\elements\Label;
use Frago9876543210\EasyForms\elements\Slider;
use Frago9876543210\EasyForms\elements\Toggle;
use Frago9876543210\EasyForms\forms\CustomForm;
use Frago9876543210\EasyForms\forms\CustomFormResponse;
use Frago9876543210\EasyForms\forms\MenuForm;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\network\mcpe\protocol\AnimatePacket;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\tile\Sign;
use pocketmine\utils\TextFormat;
use xenialdan\libnbs\Song;
use xxAROX\StimoCommunity\item\ActionItem;
use xxAROX\StimoCommunity\item\Firework;
use xxAROX\StimoCommunity\screenbox\Screenbox;
use xxAROX\StimoCommunity\screenbox\Setup;


/**
 * Class Listener
 * @package xxAROX\StimoCommunity
 * @author xxAROX
 * @date 10.11.2020 - 04:34
 * @project StimoCommunity
 */
class Listener implements \pocketmine\event\Listener{
	static $interactCooldown = [];

	/**
	 * Function onBreak
	 * @param BlockBreakEvent $event
	 * @return void
	 * @priority HIGHEST
	 */
	public function onBreak(BlockBreakEvent $event): void{
		$player = $event->getPlayer();
		$item = $event->getItem();

		if ($item instanceof ActionItem) {
			/** @var ActionItem $item */
			$item->onBreak($player, $event->getBlock());
			$event->setCancelled(true);
		}
	}

	/**
	 * Function onInteract
	 * @param PlayerInteractEvent $event
	 * @return void
	 */
	public function onInteract(PlayerInteractEvent $event): void{
		$player = $event->getPlayer();
		$block = $event->getBlock();

		if (!$event->isCancelled()) {
			if (isset(self::$interactCooldown[$player->getName()])) {
				if (!Server::getInstance()->getTick() >= self::$interactCooldown[$player->getName()]) {
					return;
				} else {
					unset(self::$interactCooldown[$player->getName()]);
				}
			}
			if (StimoCommunity::getStage()->isMusicDeskPosition($block->asVector3())) {
				if ($player->hasPermission("xxarox.community.use")) {
					$this->musicPlayerForm($player);
				} else {
					$player->sendMessage(StimoCommunity::PREFIX . "§cNo permission!");
				}
				$this->doSwing($player);
				self::$interactCooldown[$player->getName()] = Server::getInstance()->getTick() +(20 * 1);
			}
			else if (StimoCommunity::getStage()->isSettingsPosition($block->asVector3())) {
				if ($player->hasPermission("xxarox.community.use")) {
					$this->stageSettings($player);
				} else {
					$player->sendMessage(StimoCommunity::PREFIX . "§cNo permission!");
				}
				$this->doSwing($player);
				self::$interactCooldown[$player->getName()] = Server::getInstance()->getTick() +(20 * 1);
			}
			else if (($tile = $block->getLevel()->getTile($block->asVector3())) instanceof Sign) {
				if (isset(StimoCommunity::$screenBoxes["{$block->x}:{$block->y}:{$block->z}"])) {
					$screenBox = StimoCommunity::$screenBoxes["{$block->x}:{$block->y}:{$block->z}"];
					if ($screenBox instanceof Screenbox) {
						$this->doSwing($player);
						foreach (StimoCommunity::$screenBoxes as $strPos => $box) {
							if ($box->isInQueue($player) && $box !== $screenBox) {
								$player->sendMessage(StimoCommunity::PREFIX . "§cYou are already in a screenbox queue.");
								return;
							}
						}
						if ($screenBox->getYouTuber() == null) {
							if ($event->getPlayer()->hasPermission("xxarox.community.screenbox")) {
								$screenBox->setYouTuber($event->getPlayer());
								return;
							}
						} else {
							if (!$screenBox->getPlayer() instanceof Player) {
								$screenBox->setPlayer($player);
							} else {
								if ($screenBox->isInQueue($player)) {
									$screenBox->removeFromQueue($player);
									$player->sendMessage(StimoCommunity::PREFIX . "§cLeft screenbox queue.");
								} else {
									$screenBox->addToQueue($player);
									$player->sendMessage(StimoCommunity::PREFIX . "§aJoined screenbox queue.");
								}
							}
						}
						self::$interactCooldown[$player->getName()] = Server::getInstance()->getTick() +(20 * 1);
					}
				}
			}
		}
	}

	private function doSwing(Player $player): void{
		$pk = new AnimatePacket();
		$pk->action = AnimatePacket::ACTION_SWING_ARM;
		$pk->entityRuntimeId = $player->getId();
		$player->sendDataPacket($pk);
	}

	/**
	 * Function musicPlayerForm
	 * @param Player $player
	 * @return void
	 */
	private function musicPlayerForm(Player $player): void{
		$buttons = [];
		$musicPlayer = StimoCommunity::getStage()->getMusicPlayer();

		$buttons[] = (!$musicPlayer->isPause() ? "Pause" : "Resume");
		$buttons[] = "Volume";
		$buttons[] = ($musicPlayer->isPlaying() ? "Stop" : "Play");
		$buttons[] = "Toggle loop";
		$buttons[] = "Skip";
		$buttons[] = "Before";
		$buttons[] = "Select song";

		$player->sendForm(new MenuForm(
			"Music Player",
			($musicPlayer->getActiveSong() instanceof Song ? $musicPlayer->getActiveSong()->getInfo() : "§3§oNo song playing right now"),
			$buttons,
			function (Player $player, Button $button): void{
				$musicPlayer = StimoCommunity::getStage()->getMusicPlayer();
				switch ($button->getText()) {
					case "Pause":
						$musicPlayer->pause();
						$player->sendMessage(StimoCommunity::PREFIX . "Paused the music.");
						break;
					case "Resume":
						$musicPlayer->resume();
						$player->sendMessage(StimoCommunity::PREFIX . "Resumed the music.");
						break;
					case "Volume":
						$this->selectVolume($player);
						break;
					case "Stop":
						$musicPlayer->stop();
						$player->sendMessage(StimoCommunity::PREFIX . "Stopped the music.");
						break;
					case "Play":
						$musicPlayer->skip();
						$player->sendMessage(StimoCommunity::PREFIX . "Playing music.");
						break;
					case "Skip":
						$musicPlayer->skip();
						$player->sendMessage(StimoCommunity::PREFIX . "Skipped the song.");
						break;
					case "Before":
						$musicPlayer->before();
						$player->sendMessage(StimoCommunity::PREFIX . "Before the song.");
						break;
					case "Select song":
						$this->selectSong($player);
						break;
					case "Toggle loop":
						StimoCommunity::getStage()->getMusicPlayer()->toggleLoop();
						$player->sendMessage(StimoCommunity::PREFIX . (StimoCommunity::getStage()->getMusicPlayer() ? "Loop enabled." : "Loop disabled."));
						break;
				}
			}
		));
	}

	/**
	 * Function selectSong
	 * @param Player $player
	 * @return void
	 */
	private function selectSong(Player $player): void{
		$buttons = ["§cBack"];
		$arr = [];
		foreach (StimoCommunity::getStage()->getMusicPlayer()->getSongList() as $i => $song) {
			$songName = basename($song->getPath(), ".nbs");
			$arr[$songName] = $song;
			$buttons[] = $songName;
		}
		$player->sendForm(new MenuForm(
			"Select song",
			"",
			$buttons,
			function (Player $player, Button $button) use ($arr): void{
				if ($button->getText() != "§cBack") {
					StimoCommunity::getStage()->getMusicPlayer()->playNext($arr[TextFormat::clean($button->getText())]);
					$player->sendMessage(StimoCommunity::PREFIX . "Playing " . $arr[TextFormat::clean($button->getText())]->getTitle());
				} else {
					$this->musicPlayerForm($player);
				}
			},
			function (Player $player): void{
				$this->musicPlayerForm($player);
			}
		));
	}

	/**
	 * Function selectVolume
	 * @param Player $player
	 * @return void
	 */
	private function selectVolume(Player $player): void{
		$player->sendForm(new CustomForm(
			"Select volume",
			[
				new Slider("Volume", 1, 100, 5),
			],
			function (Player $player, CustomFormResponse $response): void{
				$volume = $response->getSlider()->getValue();
				StimoCommunity::getStage()->getMusicPlayer()->setVolume($volume);
				$player->sendMessage(StimoCommunity::PREFIX . "Volume set to " . $volume . "%");
			},
			function (Player $player): void{
				$this->musicPlayerForm($player);
			}
		));
	}

	/**
	 * Function stageSettings
	 * @param Player $player
	 * @return void
	 */
	private function stageSettings(Player $player): void{
		$translator = [
			"Small sphere" => Firework::TYPE_SMALL_SPHERE,
			"Star"         => Firework::TYPE_STAR,
			"Huge sphere"  => Firework::TYPE_HUGE_SPHERE,
			"Creeper Head" => Firework::TYPE_CREEPER_HEAD,
			"Burst"        => Firework::TYPE_BURST,
		];
		$translatorInt = [
			0 => Firework::TYPE_SMALL_SPHERE,
			1 => Firework::TYPE_STAR,
			2 => Firework::TYPE_HUGE_SPHERE,
			3 => Firework::TYPE_CREEPER_HEAD,
			4 => Firework::TYPE_BURST,
		];
		$colors = [
			"Black"  => Firework::COLOR_BLACK,
			"Red" => Firework::COLOR_RED,
			"Dark Green" => Firework::COLOR_DARK_GREEN,
			"Brown" => Firework::COLOR_BROWN,
			"Blue" => Firework::COLOR_BLUE,
			"Dark Purple" => Firework::COLOR_DARK_PURPLE,
			"Dark Aqua" => Firework::COLOR_DARK_AQUA,
			"Gray" => Firework::COLOR_GRAY,
			"Dark Gray" => Firework::COLOR_DARK_GRAY,
			"Pink" => Firework::COLOR_PINK,
			"Green" => Firework::COLOR_GREEN,
			"Yellow" => Firework::COLOR_YELLOW ,
			"Aqua" => Firework::COLOR_LIGHT_AQUA,
			"Magenta" => Firework::COLOR_DARK_PINK,
			"Orange" => Firework::COLOR_GOLD,
			"White" => Firework::COLOR_WHITE,
			"Random" => "random",
		];
		$colorsInt = [
			0  => Firework::COLOR_BLACK,
			1  => Firework::COLOR_RED,
			2  => Firework::COLOR_DARK_GREEN,
			3  => Firework::COLOR_BROWN,
			4  => Firework::COLOR_BLUE,
			5  => Firework::COLOR_DARK_PURPLE,
			6  => Firework::COLOR_DARK_AQUA,
			7  => Firework::COLOR_GRAY,
			8  => Firework::COLOR_DARK_GRAY,
			9  => Firework::COLOR_PINK,
			10 => Firework::COLOR_GREEN,
			11 => Firework::COLOR_YELLOW,
			12 => Firework::COLOR_LIGHT_AQUA,
			13 => Firework::COLOR_DARK_PINK,
			14 => Firework::COLOR_GOLD,
			15 => Firework::COLOR_WHITE,
			16 => "random",
		];
		$player->sendForm(new CustomForm(
			"Stage Settings",
			[
				new Label("§2Fireworks:"),
				new Toggle("Enabled", StimoCommunity::getStage()->fireworksEnabled),
				new Dropdown("Type", array_keys($translator), array_flip($translatorInt)[StimoCommunity::getStage()->fireworkSettings["type"]]),
				new Dropdown("Color", array_keys($colors), array_flip($colorsInt)[StimoCommunity::getStage()->fireworkSettings["color"]]),
				new Toggle("Fade", StimoCommunity::getStage()->fireworkSettings["fade"]),
				new Toggle("Flicker", StimoCommunity::getStage()->fireworkSettings["flicker"]),
				new Slider("Delay(in seconds)", 0.3, 2, 0.1, (StimoCommunity::getStage()->fireworkDelay /10)),
				new Label("§2Volcanoes:"),
				new Toggle("Enable", StimoCommunity::getStage()->vulkanEnabled),
			],
			function (Player $player, CustomFormResponse $response) use ($translator,$translatorInt,$colors,$colorsInt): void{
				StimoCommunity::getStage()->fireworksEnabled = $response->getToggle()->getValue();
				StimoCommunity::getStage()->fireworkSettings["type"] = $translator[$response->getDropdown()->getSelectedOption()];
				StimoCommunity::getStage()->fireworkSettings["color"] = $colors[$response->getDropdown()->getSelectedOption()];
				StimoCommunity::getStage()->fireworkSettings["fade"] = $response->getToggle()->getValue();
				StimoCommunity::getStage()->fireworkSettings["flicker"] = $response->getToggle()->getValue();
				StimoCommunity::getStage()->fireworkDelay = ($response->getSlider()->getValue() * 10);
				StimoCommunity::getStage()->vulkanEnabled = $response->getToggle()->getValue();
			},
			function (Player $player): void{
				$this->musicPlayerForm($player);
			}
		));
	}

	/**
	 * Function onQuit
	 * @param PlayerQuitEvent $event
	 * @return void
	 */
	public function onQuit(PlayerQuitEvent $event): void{
		$player = $event->getPlayer();
		if (Setup::$player == $player->getName()) {
			Setup::reset();
			return;
		}
		foreach (StimoCommunity::$screenBoxes as $strPos => $screenBox) {
			if ($screenBox->getYouTuber() instanceof Player && $screenBox->getYouTuber()->getName() == $player->getName()) {
				$screenBox->clearBox(true);
			}
			if ($screenBox->getPlayer() instanceof Player && $screenBox->getPlayer()->getName() == $player->getName()) {
				$screenBox->clearBox(false);
			}
		}
	}
}
