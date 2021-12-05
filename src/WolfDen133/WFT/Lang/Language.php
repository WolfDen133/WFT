<?php


namespace WolfDen133\WFT\Lang;

use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;
use WolfDen133\WFT\WFT;

class Language
{
    private string $name;
    private Config $config;

    public function __construct(string $name)
    {
        $this->name = $name;
        $this->config = new Config(WFT::getInstance()->getDataFolder() . "lang/" . $name . ".yml");
    }

    public function getName () : string
    {
        return $this->name;
    }

    public function getMessage (string $key, array $wildcards = []) : string
    {
        if (is_null(($text = ($this->config->get("message"))[$key]))) return "message." . $key;

        foreach ($wildcards as $find => $replace) $text = str_replace($find, $replace, $text);

        return $this->getPrefix() . TextFormat::RESET . TextFormat::YELLOW . " > " . TextFormat::RESET . TextFormat::colorize($text);
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function getValue (string $key)
    {
        if (is_null(($value = $this->config->getNested($key)))) return $key;

        if (is_string($value)) $value = TextFormat::colorize($value);

        return $value;
    }

    public function getPrefix () : string
    {
        return TextFormat::colorize($this->config->get("prefix"));
    }

    public function getFormText (string $key, array $wildcards = []) : string
    {
        if (is_null(($text = $this->config->getNested('form.' . $key)))) return $key;

        foreach ($wildcards as $find => $replace) $text = str_replace($find, $replace, $text);

        return TextFormat::colorize($text);
    }
}