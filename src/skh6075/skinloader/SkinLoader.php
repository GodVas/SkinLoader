<?php

declare(strict_types=1);

namespace skh6075\skinloader;

use pocketmine\entity\Skin;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\SingletonTrait;
use skh6075\skinloader\command\SkinLibCommand;
use skh6075\skinloader\task\async\SkinSettingAsyncTask;
use skh6075\skinloader\utils\SkinLoaderTrait;

final class SkinLoader extends PluginBase{
	use SingletonTrait, SkinLoaderTrait;

	/** @var Skin[] */
	private array $skinData = [];

	protected function onLoad() : void{
		self::$instance = $this;
	}

	protected function onEnable() : void{
		$path = $this->getDataFolder() . "Skins/";
		if(!mkdir($path) && !is_dir($path)){
			throw new \RuntimeException('Directory "' . $path . '" was not created');
		}

		$files = array_diff(scandir($path), ['.', '..']);
		$files = array_filter(array_map(static function(string $filename) {
			return pathinfo($filename, PATHINFO_EXTENSION) === "yml" ? $filename : null;
		}, $files));
		if(count($files) > 0){
			$this->getServer()->getAsyncPool()->submitTask(new SkinSettingAsyncTask($path, $files));
		}
		$this->getServer()->getCommandMap()->register(strtolower($this->getName()), new SkinLibCommand($this));
	}

	protected function onDisable() : void{
		foreach($this->skinData as $name => $skin){
			file_put_contents($this->getDataFolder() . "SKins/" . $name . ".yml", yaml_emit($this->parseCreateSkinData($name, $skin), YAML_UTF8_ENCODING));
		}
	}
}