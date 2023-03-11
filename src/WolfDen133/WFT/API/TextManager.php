<?php

namespace WolfDen133\WFT\API;

use pocketmine\world\Position;
use pocketmine\world\World;
use pocketmine\player\Player;
use pocketmine\utils\Config;
use WolfDen133\WFT\Texts\FloatingText;
use WolfDen133\WFT\Utils\Utils;
use WolfDen133\WFT\WFT;

class TextManager {

    /** @var FloatingText[] */
    public array $texts = [];

    private TextActions $actions;

    public static string $textDir;

    public function __construct()
    {
        self::$textDir = WFT::getInstance()->getDataFolder() . "texts/";
        if (!is_dir(self::$textDir)) mkdir(self::$textDir);

        $this->actions = new TextActions($this);
        $this->loadFloatingTexts();
    }

    /**
     * Load all saved texts
     *
     * @return void
     */
    public function loadFloatingTexts () : void
    {
        $dir = scandir(self::$textDir);

        foreach ($dir as $file) {
            if (in_array($file, [".", ".."])) continue;

            $config = new Config(self::$textDir . $file, Config::JSON);

            if (isset($this->texts[strtolower($config->get("name"))])) continue;

            if (!$this->levelCheck($config->get("world"))) continue;

            $position = new Position(
                $config->get("x"),
                $config->get("y"),
                $config->get("z"),
                WFT::getInstance()->getServer()->getWorldManager()->getWorldByName((string)$config->get("world"))
            );

            $this->registerText(Utils::steriliseIdentifier($config->get("name")), implode("#", $config->get("lines")), $position);

        }
    }

    /**
     * Simple function to load and check if a level is loaded
     * We do this because you cannot include an unloaded world in a spawn packet.
     *
     * @param string $levelName
     * @return bool
     */
    public function levelCheck (string $levelName) : bool
    {
        if (!WFT::getInstance()->getServer()->getWorldManager()->isWorldLoaded($levelName)) {
            WFT::getInstance()->getServer()->getWorldManager()->loadWorld($levelName);
            if (WFT::getInstance()->getServer()->getWorldManager()->isWorldLoaded($levelName)) return true;

            return false;
        }
        return true;
    }


    /**
     * Create a floating text and register it to the database
     *
     * @param string $identifier    Unique identifier that will be used to identify the text you wish to manipulate
     * @param string $text          The actual initial content of the floating text
     * @param Position $position    Where the floating text actually exists on the server
     * @param bool $spawnToAll      (optional) Whether the text is spawned to the server when it is created
     * @param bool $saveText        (optional) Whether there is a config saved to plugin_data (If you are using the api externally, disable this as it is usually only for in-game creation)
     * @return FloatingText
     */
    public function registerText (string $identifier, string $text, Position $position, bool $spawnToAll = true, bool $saveText = true) : FloatingText
    {
        $id = Utils::steriliseIdentifier($identifier);

        $floatingText = new FloatingText($id, $text, $position);
        $this->texts[$id] = $floatingText;

        if ($saveText) $this->saveText($floatingText);
        if ($spawnToAll) $this->getActions()->spawnToAll($id);

        Utils::sendCommandDataPacket();

        return $floatingText;
    }


    public function saveText (FloatingText $floatingText) : void
    {
        $config = new Config(self::$textDir . $floatingText->getName() . ".json", Config::JSON);

        $config->set("name", Utils::steriliseIdentifier($floatingText->getName()));
        $config->set("lines", explode("#", $floatingText->getText()));
        $config->set("world", $floatingText->getPosition()->getWorld()->getFolderName());
        $config->set("x", $floatingText->getPosition()->getX());
        $config->set("y", $floatingText->getPosition()->getY());
        $config->set("z", $floatingText->getPosition()->getZ());

        $config->save();
    }


    /**
     * Completely delete a text from the plugin
     *
     * @param string $identifier    Unique identifier for the text you want to remove
     */
    public function removeText (string $identifier) : void
    {
        $id = Utils::steriliseIdentifier($identifier);

        if (is_file(self::$textDir . + $id . ".json")) unlink(self::$textDir . $id . ".json");

        $this->getActions()->closeToAll($id);

        unset($this->texts[$id]);

        Utils::sendCommandDataPacket();
    }


    /**
     * Handle in-game text spawning to player, for given level
     *
     * @param Player $player
     * @param World|null $destination
     * @return void
     */
    public function spawnHandle (Player $player, World $destination = null) : void
    {
        if (is_null($destination)) $destination = $player->getWorld();

        foreach ($this->texts as $id => $text) {
            $this->getActions()->closeTo($player, $id);

            if ($destination->getFolderName() !== $text->getPosition()->getWorld()->getFolderName()) continue;

            $this->getActions()->spawnTo($player, $id);
        }
    }

    /**
     * @param string $id
     * @return FloatingText|null
     */
    public function getTextById (string $id) : ?FloatingText
    {
        return $this->texts[Utils::steriliseIdentifier($id)] ?? null;
    }

    /**
     * @return FloatingText[]
     */
    public function getTexts(): array
    {
        return $this->texts;
    }

    /**
     * @return TextActions
     */
    public function getActions(): TextActions
    {
        return $this->actions;
    }



}
