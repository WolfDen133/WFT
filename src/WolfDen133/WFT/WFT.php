<?php

declare(strict_types=1);

namespace WolfDen133\WFT;

use pocketmine\plugin\PluginBase;
use WolfDen133\WFT\API\TextManager;
use WolfDen133\WFT\Command\WFTCommand;
use WolfDen133\WFT\Exception\WFTException;
use WolfDen133\WFT\Form\FormManager;
use WolfDen133\WFT\Lang\LanguageManager;
use WolfDen133\WFT\Task\UpdateTask;
use WolfDen133\WFT\Utils\Time;
use WolfDen133\WFT\Utils\Utils;

class WFT extends PluginBase
{
    private const CONFIG_VERSION = 1;
    public static bool $display_identifier = false;
    private static self $instance;
    private TextManager $textManager;
    private LanguageManager $languageManager;
    private FormManager $formManager;

    public function onLoad() : void
    {
        $this->saveDefaultConfig();

        if ($this->getConfig()->get("config-version") !== self::CONFIG_VERSION) throw new WFTException("Invalid config version detected: Please delete current config, and restart the server");

        self::$instance = $this;

        self::$display_identifier = $this->getConfig()->get("display-identifier");

        Utils::updateOldTexts();

        $this->textManager = new TextManager();
        $this->languageManager = new LanguageManager($this);
        $this->formManager = new FormManager();

        new Time($this->getConfig()->get("timezone"), $this->getConfig()->get("date-format"), $this->getConfig()->get("time-format"));
    }

    public function onEnable() : void
    {
        $this->textManager->loadFloatingTexts();

        $this->getServer()->getPluginManager()->registerEvents(new EventListener(), $this);
        $this->getServer()->getCommandMap()->register("WFT", new WFTCommand("wft"));

        $this->getScheduler()->scheduleRepeatingTask(new UpdateTask(), 1);
    }

    public function getTextManager () : TextManager
    {
        return $this->textManager;
    }

    public function getLanguageManager () : LanguageManager
    {
        return $this->languageManager;
    }

    public function getFormManager () : FormManager
    {
        return $this->formManager;
    }

    public static function getInstance () : self
    {
        return self::$instance;
    }

}
