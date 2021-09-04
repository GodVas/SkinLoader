<?php

declare(strict_types=1);

namespace skh6075\skinloader\utils;

use JetBrains\PhpStorm\Pure;
use pocketmine\entity\Skin;

trait SkinLoaderTrait{

	#[Pure]
	public function parseCreateSkinData(string $name, Skin $skin): array{
		return [
			$name,
			base64_encode($skin->getSkinId()),
			base64_encode($skin->getSkinData()),
			base64_encode($skin->getCapeData()),
			base64_encode($skin->getGeometryName()),
			base64_encode($skin->getGeometryData())
		];
	}

	public function getSkinByName(string $name): ?Skin{
		return $this->skinData[$name] ?? null;
	}

	public function saveSkin(string $name, Skin $skin): void{
		if(isset($this->skinData[$name])){
			return;
		}
		$this->skinData[$name] = $skin;
	}

	public function removeSkin(string $name): void{
		if(!isset($this->skinData[$name])){
			return;
		}
		unset($this->skinData[$name]);
	}
}