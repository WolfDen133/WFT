<?php

declare(strict_types=1);

namespace WolfDen133\WFT;

use pocketmine\level\Position;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use RecursiveDirectoryIterator;
use WolfDen133\WFT\Command\WFTCommand;
use WolfDen133\WFT\Task\UpdateTask;
use WolfDen133\WFT\Texts\FloatingText;

class WFT extends PluginBase{

    public static bool $display_identifier = false;
    public static self $instance;

    public static API $api;


    public function onLoad() : void
    {
        $this->saveDefaultConfig();
    }

    public function onEnable() : void
    {
        self::$instance = $this;

        $timezone = (string)$this->getConfig()->get("timezone");
        date_default_timezone_set($timezone);

        if (!is_dir($this->getDataFolder() . "texts/")) mkdir($this->getDataFolder() . "texts/");

        self::$display_identifier = $this->getConfig()->get("display-identifier");
        self::$api = new API();

        $this->getServer()->getPluginManager()->registerEvents(new EventListener(), $this);
        $this->getServer()->getCommandMap()->register("WFT", new WFTCommand("wft", $this));

        $this->getScheduler()->scheduleRepeatingTask(new UpdateTask(), 1);

        $this->loadFloatingTexts();
    }

    public function loadFloatingTexts () : void
    {
        $dir = new RecursiveDirectoryIterator($this->getDataFolder() . "texts/");

        foreach ($dir as $file) {
            if ($file->getFilename() == "." or $file->getFilename() == "..") continue;

            $config = new Config($this->getDataFolder() . "texts/" . $file->getFilename(), Config::JSON);

            $this->levelCheck($config->get("level"));

            $position = new Position(
                $config->get("x"),
                $config->get("y"),
                $config->get("z"),
                $this->getServer()->getLevelByName($config->get("level"))
            );

            $text = new FloatingText($position, $config->get("name"), implode("#", $config->get("lines")));
            self::$api->registerText($text);
        }
    }

    public function levelCheck (string $levelName) : void
    {
        if ($this->getServer()->isLevelLoaded($levelName)) $this->getServer()->loadLevel($levelName);
    }

    public static function getAPI () : API
    {
        return self::$api;
    }

    public static function getInstance () : self
    {
        return self::$instance;
    }

}
