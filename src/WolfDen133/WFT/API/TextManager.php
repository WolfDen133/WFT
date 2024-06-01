<?php

namespace WolfDen133\WFT\API;

use pocketmine\player\Player;
use pocketmine\utils\Config;
use pocketmine\world\Position;
use pocketmine\world\World;
use WolfDen133\WFT\Exception\WFTException;
use WolfDen133\WFT\Texts\FloatingText;
use WolfDen133\WFT\Utils\Utils;
use WolfDen133\WFT\WFT;
use JsonException;

class TextManager {

    public const ConfigVersion = 1;

    public static string $textDir;
    /** @var FloatingText[] */
    public array $texts = [];
    private TextActions $actions;

    public function __construct()
    {
        self::$textDir = WFT::getInstance()->getDataFolder() . "texts/";
        if (!is_dir(self::$textDir)) mkdir(self::$textDir);

        $this->actions = new TextActions($this);
    }

    /**
     * Load all saved texts from disk
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


            $this->registerText(Utils::steriliseIdentifier($config->get("name")), implode("\#", $config->get("lines")), $position, true, true, (bool)$config->get("isOp"));
        }
    }

    /**
     * Simple function to load and check if a level is loaded
     * We do this because you cannot include an unloaded world in a spawn packet.
     *
     * @param string $levelName     The level to be checked
     * @return bool                 If the level is or has been loaded
     */
    public function levelCheck (string $levelName) : bool
    {
        if (empty($levelName)) return false;
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
     * @param string $identifier Unique identifier that will be used to identify the text you wish to manipulate
     * @param string $text The actual initial content of the floating text
     * @param Position $position Where the floating text actually exists on the server
     * @param bool $spawnToAll (optional) Whether the text is spawned to the server when it is created
     * @param bool $saveText (optional) Whether there is a config saved to plugin_data (If you are using the api externally, disable this as it is usually only for in-game creation)
     * @param bool $isOp
     * @return FloatingText
     * @throws WFTException
     */
    public function registerText (string $identifier, string $text, Position $position, bool $spawnToAll = true, bool $saveText = true, bool $isOp = false) : FloatingText
    {
        $id = Utils::steriliseIdentifier($identifier);

        $floatingText = new FloatingText($id, str_replace("\\n", "\n", $text), $position, $isOp);
        $this->texts[$id] = $floatingText;

        if ($saveText) $this->saveText($floatingText);
        if ($spawnToAll) $this->getActions()->spawnToAll($id);

        Utils::sendCommandDataPacket();

        return $floatingText;
    }

    /**
     * @param FloatingText $floatingText The floating text you wish to save
     * @return void
     */
    public function saveText (FloatingText $floatingText) : void
    {
        $config = new Config(self::$textDir . $floatingText->getName() . ".json", Config::JSON);

        $config->setAll([
            "ver" => self::ConfigVersion,
            "name" => Utils::steriliseIdentifier($floatingText->getName()),
            "lines" => explode("\#", $floatingText->getText()),
            "world" => $floatingText->getPosition()->getWorld()->getFolderName(),
            "x" => $floatingText->getPosition()->getX(),
            "y" => $floatingText->getPosition()->getY(),
            "z" => $floatingText->getPosition()->getZ(),
            "isOp" => $floatingText->isOperator,
        ]);

        try {
            $config->save();
        } catch (JsonException)
        {
            WFT::getInstance()->getLogger()->error("Error saving " . $floatingText->getName() . " to disk, try closing any applications that are holding the file and restart the server.");
        }

    }


    /**
     * Completely delete a text from the plugin
     *
     * @param string $identifier Unique identifier for the text you want to remove
     * @throws WFTException
     */
    public function removeText (string $identifier) : void
    {
        $id = Utils::steriliseIdentifier($identifier);

        if (is_file(self::$textDir . $id . ".json")) unlink(self::$textDir . $id . ".json");

        $this->getActions()->closeToAll($id);

        unset($this->texts[$id]);

        Utils::sendCommandDataPacket();
    }


    /**
     * Reloads all configs from disk and respawns them to the online players (used so you don't have to restart every time you change a text config)
     * @throws WFTException
     */
    public function reload () : void
    {
        foreach ($this->getTexts() as $text) $this->getActions()->closeToAll($text->getName());

        $this->texts = [];
        $this->loadFloatingTexts();

        foreach (WFT::getInstance()->getServer()->getOnlinePlayers() as $player) $this->spawnHandle($player);
    }


    /**
     * Handle in-game text spawning to player, for given level
     *
     * @param Player $player
     * @param World|null $destination
     * @return void
     * @throws WFTException
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
     * Get a text by its identifier
     *
     * @param string $id
     * @return FloatingText|null
     */
    public function getTextById (string $id) : ?FloatingText
    {
        return $this->texts[Utils::steriliseIdentifier($id)] ?? null;
    }


    /**
     * Get all the floating texts with their identifier as the index
     *
     * @return array<string, FloatingText[]>
     */
    public function getTexts(): array
    {
        return $this->texts;
    }


    /**
     * Get all the floating texts with an integer indexed list
     *
     * @return array<int, FloatingText[]>
     */
    public function getIndexedList() : array
    {
        $texts = [];
        foreach ($this->texts as $text) $texts[] = $text;
        return $texts;
    }


    /**
     * Get the actions class for controlling display status
     *
     * @return TextActions
     */
    public function getActions(): TextActions
    {
        return $this->actions;
    }
}