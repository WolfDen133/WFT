<?php

namespace WolfDen133\WFT;

use pocketmine\event\entity\EntityLevelChangeEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\Player;

class EventListener implements Listener
{

    public function onPlayerJoinEvent (PlayerJoinEvent $event) : void
    {
        WFT::getAPI()->spawnHandle($event->getPlayer());
    }

    public function onPlayerQuitEvent (PlayerQuitEvent $event) : void
    {
        foreach (WFT::getAPI()->getTexts() as $text) WFT::getAPI()::closeTo($event->getPlayer(), $text);
    }

    public function onPlayerLevelChangeEvent (EntityLevelChangeEvent $event) : void
    {
        if (!($event->getEntity() instanceof Player)) return;

        /** @var Player $player */
        $player = $event->getEntity();

        WFT::getAPI()->spawnHandle($player, $event->getTarget());
    }
}
