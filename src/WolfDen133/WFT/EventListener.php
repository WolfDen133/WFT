<?php

namespace WolfDen133\WFT;

use pocketmine\event\entity\EntityTeleportEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\server\CommandEvent;
use pocketmine\event\server\DataPacketSendEvent;
use pocketmine\event\world\WorldLoadEvent;
use pocketmine\network\mcpe\protocol\AvailableCommandsPacket;
use pocketmine\network\mcpe\protocol\types\PlayerPermissions;
use pocketmine\permission\DefaultPermissionNames;
use pocketmine\permission\DefaultPermissions;
use pocketmine\player\Player;
use pocketmine\scheduler\ClosureTask;
use pocketmine\Server;
use WolfDen133\WFT\Utils\Utils;

class EventListener implements Listener
{

    public function onPlayerJoinEvent (PlayerJoinEvent $event) : void
    {
        WFT::getInstance()->getTextManager()->spawnHandle($event->getPlayer());
    }

    public function onPlayerQuitEvent (PlayerQuitEvent $event) : void
    {
        foreach (WFT::getInstance()->getTextManager()->getTexts() as $id => $text) WFT::getInstance()->getTextManager()->getActions()->closeTo($event->getPlayer(), $id);
    }

    public function onPlayerLevelChangeEvent (EntityTeleportEvent $event) : void
    {
        if (!($event->getEntity() instanceof Player)) return;
        if ($event->getFrom()->getWorld()->getId() == $event->getTo()->getWorld()->getId()) return;

        /** @var Player $player */
        $player = $event->getEntity();

        WFT::getInstance()->getTextManager()->spawnHandle($player, $event->getTo()->getWorld());
    }

    public function onDataPacketSendEvent (DataPacketSendEvent $event) : void
    {
        foreach ($event->getPackets() as $packet) {
            if (!($packet instanceof AvailableCommandsPacket)) continue;

            Utils::setCommandPacketData($packet);
        }
    }

    public function onWorldLoadEvent (WorldLoadEvent $event) : void
    {
        WFT::getInstance()->getTextManager()->loadFloatingTexts();
    }

    public function onCommandEvent (CommandEvent $event) : void
    {
        $command = str_replace("/", "", strtolower($event->getCommand()));
        $split = explode(" ", $command);

        if (!in_array($split[0], ["op", "deop"])) return;
        if (!$event->getSender()->hasPermission(DefaultPermissions::ROOT_OPERATOR)) return;

        array_shift($split);
        $name = implode(" ", $split);

        if (($player = WFT::getInstance()->getServer()->getPlayerByPrefix($name)) != null)
            WFT::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function () use ($player) {
                Utils::handleOperatorChange($player);
            }), 5);
    }
}
