<?php

namespace WolfDen133\WFT\Utils;

use pocketmine\network\mcpe\protocol\AvailableCommandsPacket;
use pocketmine\network\mcpe\protocol\types\command\CommandEnum;
use pocketmine\network\mcpe\protocol\types\command\CommandParameter;
use pocketmine\player\Player;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;
use WolfDen133\WFT\Event\TagReplaceEvent;
use WolfDen133\WFT\WFT;

class Utils {

    private static AvailableCommandsPacket $packet;

    public static function getWildCards (Player $player) : array
    {
        $self = WFT::getInstance();

        return [
            "#" => "\n",
            "{NAME}" => $player->getName(),
            "{REAL_NAME}" => $player->getName(),
            "{DISPLAY_NAME}" => $player->getDisplayName(),
            "{PING}" => $player->getNetworkSession()->getPing(),
            "{ONLINE_PLAYERS}" => count($self->getServer()->getOnlinePlayers()),
            "{MAX_PLAYERS}" => $self->getServer()->getMaxPlayers(),
            "{X}" => (int)$player->getPosition()->getX(),
            "{Y}" => (int)$player->getPosition()->getY(),
            "{Z}" => (int)$player->getPosition()->getZ(),
            "{REAL_TPS}" => $self->getServer()->getTicksPerSecond(),
            "{TPS}" => $self->getServer()->getTicksPerSecondAverage(),
            "{REAL_LOAD}" => $self->getServer()->getTickUsage(),
            "{LOAD}" => $self->getServer()->getTickUsageAverage(),
            "{LEVEL_NAME}" => $player->getWorld()->getDisplayName(),
            "{LEVEL_FOLDER_NAME}" => $player->getWorld()->getFolderName(),
            "{LEVEL_PLAYERS}" => count($player->getWorld()->getPlayers()),
            "{CONNECTION_IP}" => $player->getNetworkSession()->getIp(),
            "{SERVER_IP}" => $self->getServer()->getIP(),
            "{TIME}" => date($self->getConfig()->get("time-format")),
            "{DATE}" => date($self->getConfig()->get("date-format"))
        ];
    }

    public static function getFormattedText (string $rawtext, Player $player) : string
    {
        $wildcards = self::getWildCards($player);
        $text = TextFormat::colorize($rawtext);

        foreach ($wildcards as $find=>$replace) $text = str_replace($find, $replace, $text);

        $ev = new TagReplaceEvent($text, $player);
        $ev->call();

        return $ev->getText();
    }

    public static function updateOldTexts () : void
    {
        $path = WFT::getInstance()->getDataFolder() . "fts/";

        if (!is_dir($path)) return;

        $dir = new \RecursiveDirectoryIterator($path);
        foreach ($dir as $fileInfo) {
            if ($fileInfo->getFilename() == "." or $fileInfo->getFilename() == "..") continue;

            $config = new Config($path . $fileInfo->getFilename(), Config::YAML);

            if (!$config->exists('visible')) {
                unlink($path . $fileInfo->getFilename());
                continue;
            }

            $data = [];

            $data['name'] = $config->get('name');
            $data['x'] = (float) $config->get("x");
            $data['y'] = (float) $config->get("y");
            $data['z'] = (float) $config->get("z");
            $data['world'] = (string) $config->get("level");
            $data['lines'] = (array) $config->get("lines");

            self::regenerateConfig($data);
            unlink($path . $fileInfo->getFilename());

            WFT::getInstance()->getServer()->getLogger()->info('[WFT] >> Migration: Successfully migrated ' . $data['name'] . " floating text from WFT-OLD format.");
        }

        rmdir($path);
    }

    private static function regenerateConfig (array $data) : void
    {
        $config = new Config(WFT::getInstance()->getDataFolder() . "texts/" . $data['name'] . ".json", Config::JSON);

        $config->set("name", $data['name'] );
        $config->set("lines", $data['lines'] );
        $config->set("world", $data['world'] );
        $config->set("x", $data['x'] );
        $config->set("y", $data['y'] );
        $config->set("z", $data['z'] );

        $config->save();
    }

    public static function setCommandPacketData (AvailableCommandsPacket $packet) : void
    {
        self::$packet = $packet;

        if (!isset($packet->commandData['wft'])) return;

        $args = [
            [
                CommandParameter::enum('create', new CommandEnum('create', ['create']), CommandParameter::FLAG_FORCE_COLLAPSE_ENUM),
                CommandParameter::standard('uniqueName', AvailableCommandsPacket::ARG_TYPE_STRING, 0, true),
                CommandParameter::standard('text', AvailableCommandsPacket::ARG_TYPE_RAWTEXT, 0, true)
            ],
            [
                CommandParameter::enum('remove', new CommandEnum('remove', ['remove']), CommandParameter::FLAG_FORCE_COLLAPSE_ENUM),
                CommandParameter::enum('uniqueName', new CommandEnum('uniqueName', array_keys(WFT::getInstance()::getAPI()->texts)), 0, true)
            ],
            [
                CommandParameter::enum('edit', new CommandEnum('edit', ['edit']), CommandParameter::FLAG_FORCE_COLLAPSE_ENUM),
                CommandParameter::enum('uniqueName', new CommandEnum('uniqueName', array_keys(WFT::getInstance()::getAPI()->texts)), 0, true),
                CommandParameter::standard('text', AvailableCommandsPacket::ARG_TYPE_RAWTEXT, 0, true)

            ],
            [
                CommandParameter::enum('tp', new CommandEnum('tp', ['tp']), CommandParameter::FLAG_FORCE_COLLAPSE_ENUM),
                CommandParameter::enum('uniqueName', new CommandEnum('uniqueName', array_keys(WFT::getInstance()::getAPI()->texts)), 0, true)
            ],
            [
                CommandParameter::enum('tphere', new CommandEnum('tphere', ['tphere']), CommandParameter::FLAG_FORCE_COLLAPSE_ENUM),
                CommandParameter::enum('uniqueName', new CommandEnum('uniqueName', array_keys(WFT::getInstance()::getAPI()->texts)), 0, true),
            ],
            [
                CommandParameter::enum('list', new CommandEnum('list', ['list']), CommandParameter::FLAG_FORCE_COLLAPSE_ENUM),
            ],
            [
                CommandParameter::enum('help', new CommandEnum('help', ['help']), CommandParameter::FLAG_FORCE_COLLAPSE_ENUM),
            ]
        ];

        $packet->commandData['wft']->overloads = $args;
    }

    public static function sendCommandDataPacket () : void
    {
        foreach (WFT::getInstance()->getServer()->getOnlinePlayers() as $player) $player->getNetworkSession()->sendDataPacket(self::$packet);
    }
}
