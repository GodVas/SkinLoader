<?php

declare(strict_types=1);

namespace skh6075\skinloader\task\async;

use pocketmine\entity\Skin;
use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;
use ReflectionProperty;
use skh6075\skinloader\SkinLoader;

final class SkinSettingAsyncTask extends AsyncTask{

	public function __construct(
		private string $path,
		private array $files = [],
		private array $saveSkins = []
	){}

	public function onRun() : void{
		$skins = [];
		foreach($this->files as $filename){
			$map = yaml_parse(file_get_contents($this->path . $filename));
			$skinName = array_shift($map);
			$skins[$skinName] = new Skin(...array_map(static function(string $hash) {
				return base64_decode($hash);
			}, $map));
		}
		$this->saveSkins = $skins;

		$refProperty = new ReflectionProperty(SkinLoader::class, "skinData");
		$refProperty->setAccessible(true);
		$refProperty->setValue(SkinLoader::class, $this->saveSkins);
	}

	public function onCompletion() : void{
		Server::getInstance()->getLogger()->notice("Skin load as many as " . count($this->saveSkins));
	}
}