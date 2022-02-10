<?php

namespace WolfDen133\WFT;

use pocketmine\world\World;
use pocketmine\player\Player;
use pocketmine\utils\Config;
use WolfDen133\WFT\Texts\FloatingText;
use WolfDen133\WFT\Utils\Utils;

class API {

    /** @var FloatingText[] */
    public array $texts = [];

    /**
     * @param FloatingText $floatingText
     * @param bool $spawnToAll
     */
    public function registerText (FloatingText $floatingText, bool $spawnToAll = true) : void
    {
        $this->texts[strtolower($floatingText->getName())] = $floatingText;

        if ($spawnToAll) $this->spawnToAll($floatingText);

        Utils::sendCommandDataPacket();
    }


    public function generateConfig (FloatingText $floatingText) : void
    {
        $config = new Config(WFT::getInstance()->getDataFolder() . "texts/" . $floatingText->getName() . ".json", Config::JSON);

        $config->set("name", strtolower($floatingText->getName()));
        $config->set("lines", explode("#", $floatingText->getText()));
        $config->set("world", $floatingText->getPosition()->getWorld()->getFolderName());
        $config->set("x", $floatingText->getPosition()->getX());
        $config->set("y", $floatingText->getPosition()->getY());
        $config->set("z", $floatingText->getPosition()->getZ());

        $config->save();
    }

    public function removeText (FloatingText $floatingText) : void
    {
        unlink(WFT::getInstance()->getDataFolder() . "texts/" . $floatingText->getName() . ".json");

        self::closeToAll($floatingText);

        unset($this->texts[$floatingText->getName()]);

        Utils::sendCommandDataPacket();
    }

    public function spawnHandle (Player $player, World $destination = null) : void
    {
        if (is_null($destination)) $destination = $player->getWorld();

        foreach ($this->texts as $text) {
            self::closeTo($player, $text);
            if ($destination->getFolderName() !== $text->getPosition()->getWorld()->getFolderName()) continue;

            self::spawnTo($player, $text);

        }
    }

    /**
     * @param FloatingText $floatingText
     */
    public static function spawnToAll (FloatingText $floatingText) : void
    {
        foreach (WFT::getInstance()->getServer()->getOnlinePlayers() as $player) $floatingText->spawnTo($player);
    }

    /**
     * @param FloatingText $floatingText
     */
    public static function closeToAll (FloatingText $floatingText) : void
    {
        foreach (WFT::getInstance()->getServer()->getOnlinePlayers() as $player) $floatingText->closeTo($player);
    }

    /**
     * @param FloatingText $floatingText
     */
    public static function respawnToAll (FloatingText $floatingText) : void
    {
        foreach (WFT::getInstance()->getServer()->getOnlinePlayers() as $player) $floatingText->respawnTo($player);
    }

    /**
     * @param Player $player
     * @param FloatingText $floatingText
     */
    public static function spawnTo (Player $player, FloatingText $floatingText) : void
    {
        $floatingText->spawnTo($player);
    }

    /**
     * @param Player $player
     * @param FloatingText $floatingText
     */
    public static function closeTo (Player $player, FloatingText $floatingText) : void
    {
        $floatingText->closeTo($player);
    }

    /**
     * @param Player $player
     * @param FloatingText $floatingText
     */
    public static function respawnTo (Player $player, FloatingText $floatingText) : void
    {
        $floatingText->respawnTo($player);
    }

    /**
     * @param string $name
     * @return FloatingText|null
     */
    public function getTextByName (string $name) : ?FloatingText
    {
        return $this->texts[$name] ?? null;
    }

    /**
     * @return FloatingText[]
     */
    public function getTexts(): array
    {
        return $this->texts;
    }


}