<?php

namespace WolfDen133\WFT\Lang;

use pocketmine\utils\Config;
use WolfDen133\WFT\WFT;

class LanguageManager
{
    private WFT $plugin;
    private string $dataPath;
    public array $internalLanguages = ['en', 'ru', 'ua', 'de', 'sp', 'sk', 'cz', 'tr', 'id'];

    public string $defaultLanguage = 'en';
    public Language $selectedLanguage;

    /** @var Language[] */
    private array $languages = [];

    public function __construct(WFT $plugin)
    {
        $this->plugin = $plugin;
        $this->dataPath = $this->plugin->getDataFolder() . "lang/";

        $this->dataFolderCheck();
        $this->loadLanguages();
        $this->selectLanguage();
    }

    private function dataFolderCheck () : void
    {
        if (!is_dir($this->dataPath)) mkdir($this->dataPath);

        if (!is_file($this->dataPath . $this->defaultLanguage . ".yml")) {
            $this->plugin->saveResource($this->defaultLanguage . ".yml");
            rename($this->plugin->getDataFolder() . $this->defaultLanguage . ".yml", $this->dataPath . $this->defaultLanguage . ".yml");
        }

        $this->saveLanguages();
    }

    private function saveLanguages () : void
    {
        foreach ($this->internalLanguages as $language) {
            if (is_file($this->dataPath . $language . ".yml")) continue;

            $this->plugin->saveResource($language . ".yml");
            rename($this->plugin->getDataFolder() . $language . ".yml", $this->dataPath . $language . ".yml");
        }
    }

    private function loadLanguages () : void
    {
        $dir = new \RecursiveDirectoryIterator($this->plugin->getDataFolder() . "lang/");

        foreach ($dir as $file) {
            if ($file->getFilename() == ".." or $file->getFilename() == ".") continue;

            $config = new Config($this->dataPath . $file->getFilename(), Config::YAML);
            if (is_null(($name = strtolower($config->get("name"))))) continue;

            $this->languages[$name] = new Language($name);
        }
    }

    private function selectLanguage () : void
    {
        $name = $this->plugin->getConfig()->get("language");

        if (!key_exists($name, $this->languages)) {
            $this->selectedLanguage = $this->getDefaultLanguage();
            return;
        }

        $this->selectedLanguage = $this->languages[strtolower($name)];
        $this->plugin->getServer()->getLogger()->info("[WFT] >> Language: $name");
    }

    public function getLanguage () : Language
    {
        return $this->selectedLanguage;
    }

    public function getDefaultLanguage () : Language
    {
        return $this->languages[$this->defaultLanguage];
    }
}
