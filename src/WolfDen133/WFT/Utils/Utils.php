<?php

namespace WolfDen133\WFT\Utils;

use pocketmine\player\Player;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;
use WolfDen133\WFT\Event\TagReplaceEvent;
use WolfDen133\WFT\WFT;

class Utils {

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
}
