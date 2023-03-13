<?php

namespace WolfDen133\WFT\API;

use pocketmine\player\Player;
use WolfDen133\WFT\Exception\WFTException;
use WolfDen133\WFT\Texts\FloatingText;
use WolfDen133\WFT\Utils\Utils;
use WolfDen133\WFT\WFT;

class TextActions
{
    private TextManager $textManager;

    public function __construct(TextManager $textManager)
    {
        $this->textManager = $textManager;
    }

    /**
     * Spawn a text to the whole server
     *
     * @param string $id
     */
    public function spawnToAll (string $id) : void
    {
        foreach (WFT::getInstance()->getServer()->getOnlinePlayers() as $player) $this->spawnTo($player, $id);

    }

    /**
     * Close a text to the whole server
     *
     * @param string $id
     */
    public function closeToAll (string $id) : void
    {
        foreach (WFT::getInstance()->getServer()->getOnlinePlayers() as $player) $this->closeTo($player, $id);
    }

    /**
     * Respawn a text to the whole server
     *
     * @param string $id
     */
    public function respawnToAll (string $id) : void
    {
        foreach (WFT::getInstance()->getServer()->getOnlinePlayers() as $player) $this->respawnTo($player, $id);
    }

    /**
     * Show a text to a specific player
     *
     * @param Player $player
     * @param string $id
     * @throws WFTException
     */
    public function spawnTo (Player $player, string $id) : void
    {
        $this->getText($id)->spawnTo($player);
    }

    /**
     * Hide a text to a specific player
     *
     * @param Player $player
     * @param string $id
     * @throws WFTException
     */
    public function closeTo (Player $player, string $id) : void
    {
        $this->getText($id)->closeTo($player);
    }

    /**
     * Respawn a text to a specific player
     *
     * @param Player $player
     * @param string $id
     * @throws WFTException
     */
    public function respawnTo (Player $player, string $id) : void
    {
        $this->getText($id)->respawnTo($player);
    }


    private function getText (string $id) : FloatingText
    {
        $floatingText = $this->textManager->getTextById($id);

        if (is_null($floatingText)) {
            throw new WFTException("Could not find text {$id}");
        }

        return $floatingText;
    }
}