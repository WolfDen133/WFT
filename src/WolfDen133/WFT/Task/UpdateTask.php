<?php

namespace WolfDen133\WFT\Task;

use pocketmine\scheduler\Task;
use WolfDen133\WFT\WFT;

class UpdateTask extends Task
{
    public function onRun () : void
    {
        foreach (WFT::getAPI()->getTexts() as $text) {
            foreach (WFT::getInstance()->getServer()->getOnlinePlayers() as $player) {
                $text->updateTextTo($player);
            }
        }
    }
}