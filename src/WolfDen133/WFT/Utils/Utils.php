<?php

namespace WolfDen133\WFT\Utils;

use pocketmine\network\mcpe\protocol\AvailableCommandsPacket;
use pocketmine\network\mcpe\protocol\types\command\CommandEnum;
use pocketmine\network\mcpe\protocol\types\command\CommandOverload;
use pocketmine\network\mcpe\protocol\types\command\CommandParameter;
use pocketmine\player\Player;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;
use WolfDen133\WFT\API\TextManager;
use WolfDen133\WFT\Event\TagReplaceEvent;
use WolfDen133\WFT\WFT;

class Utils
{

    private static ?AvailableCommandsPacket $packet = null;

    private static array $fileBlackList = [".", ".."];

    public static function getWildCards(Player $player): array
    {
        $self = WFT::getInstance();

        return [
            "{NAME}" => $player->getName(),
            "{REAL_NAME}" => $player->getName(),
            "{DISPLAY_NAME}" => $player->getDisplayName(),
            "{PING}" => $player->getNetworkSession()->getPing() ?? "999+",
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
            "{TIME}" => Time::getTime(),
            "{DATE}" => Time::getDate()
        ];
    }

    public static function handleOperatorChange (Player $for)
    {
        foreach (WFT::getInstance()->getTextManager()->getTexts() as $text) WFT::getInstance()->getTextManager()->getActions()->respawnTo($for, $text->getName());
    }

    public static function getFormattedText(string $rawtext, Player $player): string
    {
        $text = TextFormat::colorize($rawtext);

        $ev = new TagReplaceEvent($text, $player);
        $ev->call();

        $wildcards = self::getWildCards($player);
        $replacedText = $ev->getText();
        foreach ($wildcards as $find => $replace) $replacedText = str_replace($find, $replace, $replacedText);

        return $replacedText;
    }

    public static function updateOldTexts(): void
    {
        $path = WFT::getInstance()->getDataFolder() . "fts/";

        if (!is_dir($path)) return;

        $dir = new \RecursiveDirectoryIterator($path);
        foreach (scandir($dir) as $filename) {
            if (in_array($filename, self::$fileBlackList)) continue;

            $config = new Config($path . $filename, Config::YAML);

            if (!$config->exists('visible')) {
                unlink($path . $filename);
                continue;
            }

            $data = [];

            $data['name'] = Utils::steriliseIdentifier($config->get('name'));
            $data['x'] = (float)$config->get("x");
            $data['y'] = (float)$config->get("y");
            $data['z'] = (float)$config->get("z");
            $data['world'] = (string)$config->get("level");
            $data['lines'] = (array)$config->get("lines");
            $data['ver'] = TextManager::ConfigVersion;

            unlink($path . $filename);
            self::regenerateConfig($data);

            WFT::getInstance()->getServer()->getLogger()->info('[WFT] >> Migration: Successfully migrated ' . $data['name'] . " floating text from WFT-OLD format.");
        }

        rmdir($path);
    }

    private static function regenerateConfig(array $data): void
    {
        $config = new Config(WFT::getInstance()->getDataFolder() . "texts/" . $data['name'] . ".json", Config::JSON);

        $config->set("ver", $data['ver']);
        $config->set("name", $data['name']);
        $config->set("lines", $data['lines']);
        $config->set("world", $data['world']);
        $config->set("x", $data['x']);
        $config->set("y", $data['y']);
        $config->set("z", $data['z']);

        $config->save();
    }

    public static function steriliseIdentifier(string $id): string
    {
        $id = str_replace(" ", "_", strtolower($id));
        $id = preg_replace('/[^A-Za-z0-9_]/', '', $id);
        return preg_replace('/_+/', "_", $id);
    }

    // BLAME COMMANDO (cannot yet use dynamic enum lists, or use implode() for arguments :))
    public static function setCommandPacketData (AvailableCommandsPacket $packet) : void
    {
        if (!isset($packet->commandData['wft'])) return;

        $args = [
            new CommandOverload(false, [
                CommandParameter::enum('create', new CommandEnum('create', ['create']), CommandParameter::FLAG_FORCE_COLLAPSE_ENUM),
                CommandParameter::standard('uniqueName', AvailableCommandsPacket::ARG_TYPE_STRING, 0, true),
                CommandParameter::standard('text', AvailableCommandsPacket::ARG_TYPE_RAWTEXT, 0, true)]
            ),
            new CommandOverload(false, [
                    CommandParameter::enum('remove', new CommandEnum('remove', ['remove']), CommandParameter::FLAG_FORCE_COLLAPSE_ENUM),
                    CommandParameter::enum('uniqueName', new CommandEnum('uniqueName', array_keys(WFT::getInstance()->getTextManager()->texts)), 0, true)]
            ),
            new CommandOverload(false, [
                CommandParameter::enum('edit', new CommandEnum('edit', ['edit']), CommandParameter::FLAG_FORCE_COLLAPSE_ENUM),
                CommandParameter::enum('uniqueName', new CommandEnum('uniqueName', array_keys(WFT::getInstance()->getTextManager()->texts)), 0, true),
                CommandParameter::standard('text', AvailableCommandsPacket::ARG_TYPE_RAWTEXT, 0, true)

            ]),
            new CommandOverload(false, [
                CommandParameter::enum('tp', new CommandEnum('tp', ['tp']), CommandParameter::FLAG_FORCE_COLLAPSE_ENUM),
                CommandParameter::enum('uniqueName', new CommandEnum('uniqueName', array_keys(WFT::getInstance()->getTextManager()->texts)), 0, true)
            ]),
            new CommandOverload(false, [
                CommandParameter::enum('tphere', new CommandEnum('tphere', ['tphere']), CommandParameter::FLAG_FORCE_COLLAPSE_ENUM),
                CommandParameter::enum('uniqueName', new CommandEnum('uniqueName', array_keys(WFT::getInstance()->getTextManager()->texts)), 0, true),
            ]),
            new CommandOverload(false, [
                CommandParameter::enum('list', new CommandEnum('list', ['list']), CommandParameter::FLAG_FORCE_COLLAPSE_ENUM)]
            ),
            new CommandOverload(false, [
                CommandParameter::enum('help', new CommandEnum('help', ['help']), CommandParameter::FLAG_FORCE_COLLAPSE_ENUM)]
            ),
            new CommandOverload(false, [
                    CommandParameter::enum('reload', new CommandEnum('reload', ['reload']), CommandParameter::FLAG_FORCE_COLLAPSE_ENUM)]
            )
        ];

        $packet->commandData["wft"]->overloads = $args;

        self::$packet = $packet;
    }

    public static function sendCommandDataPacket () : void
    {
        if (self::$packet == null) return;
        foreach (WFT::getInstance()->getServer()->getOnlinePlayers() as $player) $player->getNetworkSession()->sendDataPacket(self::$packet);
    }
}
