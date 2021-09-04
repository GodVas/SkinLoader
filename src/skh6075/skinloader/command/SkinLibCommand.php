<?php

declare(strict_types=1);

namespace skh6075\skinloader\command;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\Server;
use skh6075\lib\modelskin\ModelSkin;
use skh6075\skinloader\SkinLoader;

final class SkinLibCommand extends Command{

	private const PREFIX = "§l§b[Skin]§r§7 ";

	public function __construct(private SkinLoader $plugin){
		parent::__construct("skin", "skin setting command.", "/skin");
		$this->setPermission("skin.loader.permission");
	}

	public function execute(CommandSender $player, string $label, array $args): bool{
		if(!$player instanceof Player || !$this->testPermission($player)){
			return false;
		}

		switch(array_shift($args) ?? ""){
			case "save":
				$name = array_shift($args) ?? "";
				if(trim($name) === ""){
					$player->sendMessage(self::PREFIX . $this->getUsage() . " [skinName: string]");
					return false;
				}

				if($this->plugin->getSkinByName($name) !== null){
					$player->sendMessage(self::PREFIX . "This skin already exists.");
					return false;
				}

				$this->plugin->saveSkin($name, $player->getSkin());
				$player->sendMessage(self::PREFIX . "You have successfully saved your skin as §f" . $name);
				break;
			case "send":
				$name = array_shift($args) ?? "";
				$target = array_shift($args) ?? strtolower($player->getName());
				if(trim($name) === ""){
					$player->sendMessage(self::PREFIX . $this->getUsage() . " [skinName: string] [target: default]");
					return false;
				}

				if(trim($target) !== "" && strtolower($player->getName()) !== $target){
					$_player = Server::getInstance()->getPlayerByPrefix($target);
					$target = $_player?->getName();
				}

				$target = strtolower($target);

				$skin = $this->plugin->getSkinByName($name);
				if($skin === null){
					$player->sendMessage(self::PREFIX . "Can't find skins saved with that name");
					return false;
				}

				$targetPlayer = Server::getInstance()->getPlayerExact($target);
				if($targetPlayer === null){
					$player->sendMessage(self::PREFIX . "The player is offline.");
					return false;
				}

				$targetPlayer->setSkin($skin);
				$targetPlayer->sendSkin(Server::getInstance()->getOnlinePlayers());
				$player->sendMessage(self::PREFIX . "The skin has been loaded.");
				break;
			case "model_load":
				$filename = array_shift($args) ?? "";
				if(trim($filename) === ""){
					$player->sendMessage(self::PREFIX . $this->getUsage() . " [filename: string]");
					return false;
				}

				$dir = $this->plugin->getDataFolder();
				if(!file_exists($dir . $filename . ".json") || !file_exists($dir . $filename . ".png")){
					$player->sendMessage(self::PREFIX . "Modeling could not be loaded. (.json, .png not found)");
					return false;
				}

				$skin = ModelSkin::makeGeometrySkin($player->getSkin(), $dir, $filename);
				$player->setSkin($skin);
				$player->sendSkin(Server::getInstance()->getOnlinePlayers());
				$player->sendMessage(self::PREFIX . "Modeling has been loaded.");
				break;
			default:
				$player->sendMessage(self::PREFIX . $this->getUsage() . " save [name: string] - Save the skin as an identifier name.");
				$player->sendMessage(self::PREFIX . $this->getUsage() . " send [name: string] [target: default] - Load a saved skin.");
				$player->sendMessage(self::PREFIX . $this->getUsage() . " model_load [filename: string] - Import modeling");
				break;
		}
		return true;
	}
}