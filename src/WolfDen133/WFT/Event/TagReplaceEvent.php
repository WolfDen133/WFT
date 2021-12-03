<?php

namespace WolfDen133\WFT\Event;

use pocketmine\event\Event;
use pocketmine\player\Player;

class TagReplaceEvent extends Event
{
    private string $text;
    private Player $player;

    public function __construct(string $text, Player $player)
    {
        $this->text = $text;
        $this->player = $player;
    }

    public function getPlayer () : Player
    {
        return $this->player;
    }

    public function getText () : string
    {
        return $this->text;
    }

    public function setText (string $text) : void
    {
        $this->text = $text;
    }

}