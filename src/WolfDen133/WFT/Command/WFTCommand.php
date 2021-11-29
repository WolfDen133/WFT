<?php

namespace WolfDen133\WFT\Command;

use jojoe77777\FormAPI\CustomForm;
use jojoe77777\FormAPI\SimpleForm;

use pocketmine\command\CommandSender;
use pocketmine\command\PluginCommand;

use pocketmine\level\Position;
use pocketmine\Player;

use pocketmine\plugin\Plugin;
use pocketmine\utils\TextFormat;

use WolfDen133\WFT\WFT;
use WolfDen133\WFT\Texts\FloatingText;

class WFTCommand extends PluginCommand
{
    public const MODE_EDIT = 0;
    public const MODE_TP = 1;
    public const MODE_TPHERE = 2;
    public const MODE_REMOVE = 3;

    public function __construct(string $name, Plugin $owner)
    {
        parent::__construct($name, $owner);

        $this->setPermission("wft.command.use");
        $this->setPermissionMessage(TextFormat::RED . "Unknown command. Try /help for a list of commands");
        $this->setDescription("Manage floating texts");
        $this->setAliases(["ft"]);
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args)
    {
        if (!($sender instanceof Player)) {
            $sender->sendMessage(TextFormat::RED . "Sorry, that command is for players only!");
            return;
        }

        if (!$sender->hasPermission($this->getPermission())) {
            $sender->sendMessage($this->getPermissionMessage());
            return;
        }

        if (empty($args)) {
            $sender->sendMessage($this->getUsage());
            return;
        }

        if (count($args) == 1) {
            switch ($args[0]) {
                case "list":
                case "see":
                case "all":
                    $sender->sendMessage("\\/=====FLOATING-TEXT LIST=====\\/\n");
                    foreach (WFT::getInstance()::getAPI()->getTexts() as $text) {

                        $sender->sendMessage("==============================\n" .
                        " Name: " . $text->getName() . "\n" .
                        " Level: " . $text->getPosition()->getLevel()->getName() . "\n" .
                        " Position: " . $text->getPosition()->getX() . ", " . $text->getPosition()->getY() . ", " . $text->getPosition()->getZ() . "\n" .
                        " Lines: " . "\n(\n    " . implode("\n    ", explode("#", $text->getText())) . "\n)\n" .
                        "==============================\n");

                    }
                    $sender->sendMessage("/\\=====FLOATING-TEXT LIST=====/\\");
                    break;

                case "help":
                case "stuck":
                case "h":
                case "?":

                    $sender->sendMessage("<===== WFT HELP =====>\n" . "Command: /wft <arguments: mixed>\n" . "Arguments:\n\n" .
                        " add:\n    Description: Add a floating-text\n  Usage: /wft add (<name: string> [text: string])" . "\n\n" .
                        " remove:\n    Description: Remove a floating-text\n    Usage: /wft remove (<name: string>)" . "\n\n" .
                        " edit:\n    Description: Change a floating-text's text\n    Usage: /wft edit (<name: string> [newText: string])" . "\n\n" .
                        " tp:\n     Description: Teleport to a floating-text\n    Usage: /wft tp (<name: string>)" . "\n\n" .
                        " tphere:\n    Description: Teleport a floating-text to you\n    Usage: /wft tphere (<name: string>)" . "\n\n" .
                        " help:\n    Description: Get help with WFT\n    Usage: /wft help\n<==== WFT HELP ====>");
                    break;

                case "remove":
                case "break":
                case "delete":
                case "bye":
                case "d":
                case "r":
                    $this->openListForm($sender, self::MODE_REMOVE);
                    break;

                case "tp":
                case "teleportto":
                case "tpto":
                case "goto":
                case "teleport":
                    $this->openListForm($sender, self::MODE_TP);
                    break;

                case "tphere":
                case "teleporthere":
                case "movehere":
                case "bringhere":
                case "tph":
                case "move":
                    $this->openListForm($sender, self::MODE_TPHERE);
                    break;

                case "edit":
                case "e":
                case "change":
                    $this->openListForm($sender, self::MODE_EDIT);
                    break;

                case "add":
                case "create":
                case "spawn":
                case "summon":
                case "new":
                case "c":
                case "a":
                    $this->openCreationForm($sender);
                    break;
            }

            return;
        }

        if (count($args) == 2) {

            if (($text = WFT::getAPI()->getTextByName($args[1])) === null) {
                $sender->sendMessage("Sorry, that text doesnt exist!");
                return;
            }

            switch ($args[0]) {
                case "remove":
                case "break":
                case "delete":
                case "bye":
                case "d":
                case "r":

                    WFT::getAPI()->removeText($text);
                    $sender->sendMessage("Successfully removed text " . $text->getName() . "!");

                    break;
                case "tp":
                case "teleportto":
                case "tpto":
                case "goto":
                case "teleport":

                    WFT::getInstance()->levelCheck($text->getPosition()->getLevel()->getName());
                    $sender->teleport($text->getPosition());

                    break;
                case "tphere":
                case "teleporthere":
                case "movehere":
                case "bringhere":
                case "tph":
                case "move":

                    $text->setPosition(new Position($sender->getX(), $sender->getY() + 1.8, $sender->getZ(), $sender->getLevel()));
                    WFT::getAPI()::respawnToAll($text);
                    WFT::getAPI()->generateConfig($text);

                    break;
            }

            return;
        }

        if (count($args) >= 3) {
            $api = WFT::getAPI();

            switch ($args[0]) {
                case "add":
                case "create":
                case "spawn":
                case "summon":
                case "new":
                case "c":
                case "a":

                    if (in_array($args[1], array_keys($api->getTexts()))) {
                        $sender->sendMessage("Sorry, that text already exists!");
                        return;
                    }

                    $floatingText = new FloatingText(new Position($sender->getX(), $sender->getY() + 1.8, $sender->getZ(), $sender->getLevel()), $args[1], implode(" ", array_splice($args, 2)));

                    $api->registerText($floatingText);
                    $api->generateConfig($floatingText);
                    $api::spawnToAll($floatingText);

                    $sender->sendMessage("Successfully created the text $args[1]!");
                    break;
                case "edit":
                case "e":
                case "change":

                    if (($text = WFT::getAPI()->getTextByName($args[1])) === null) {
                        $sender->sendMessage("Sorry, that text doesnt exist!");
                        return;
                    }

                    $text->setText(implode(" ", array_splice($args, 2)));
                    $api::respawnToAll($text);
                    $sender->sendMessage("Successfully updated the text $args[1]!");
                    break;
            }
        }
    }

    public function openCreationForm (Player $player) : void
    {
        $form = new CustomForm(function (Player $player, array $data = null) : void
        {
            if (is_null($data)) return;

            $api = WFT::getAPI();

            if (in_array($data[1], array_keys($api->getTexts()))) {
                $player->sendMessage("Sorry, that text already exists!");
                return;
            }

            $floatingText = new FloatingText(new Position($player->getX(), $player->getY() + 1.8, $player->getZ(), $player->getLevel()), $data[1], $data[2]);

            $api->registerText($floatingText);
            $api->generateConfig($floatingText);
            $api::spawnToAll($floatingText);

            $player->sendMessage("Successfully created the text $data[1]!");
        });

        $form->setTitle("Create a Floating-Text");
        $form->addLabel("Create a new floating text by filling out the form below.");
        $form->addInput("Name", "Text's unique name");
        $form->addInput("Text", "Text's content (use & for colors)");

        $player->sendForm($form);
    }

    public function openListForm (Player $player, int $mode) : void
    {
        $form = new SimpleForm(function (Player $player, string $data = null) use ($mode) : void
        {
            if (is_null($data)) return;

            $text = WFT::getAPI()->getTextByName($data);

            switch ($mode) {
                case self::MODE_EDIT:
                    $this->openEditForm($player, $text);
                    break;
                case self::MODE_TP:

                    WFT::getInstance()->levelCheck($text->getPosition()->getLevel()->getName());
                    $player->teleport($text->getPosition());

                    break;
                case self::MODE_TPHERE:

                    $text->setPosition(new Position($player->getX(), $player->getY() + 1.8, $player->getZ(), $player->getLevel()));
                    WFT::getAPI()::respawnToAll($text);
                    WFT::getAPI()->generateConfig($text);

                    break;
                case self::MODE_REMOVE:

                    WFT::getAPI()->removeText($text);
                    $player->sendMessage("Successfully removed text " . $text->getName() . "!");

                    break;
            }
        });

        $form->setTitle("Select a Floating-Text");

        foreach (WFT::getAPI()->getTexts() as $text) {
            $form->addButton(TextFormat::DARK_GRAY . $text->getName() . "\n" . TextFormat::GRAY . $text->getText(), -1, "", $text->getName());
        }

        $player->sendForm($form);
    }

    public function openEditForm (Player $player, FloatingText $floatingText) : void
    {
        $form = new CustomForm(function (Player $player, array $data = null) use ($floatingText) : void
        {
            if (is_null($data)) return;

            $api = WFT::getAPI();

            $floatingText->setText($data[1]);
            $api::respawnToAll($floatingText);
            $player->sendMessage("Successfully updated the text $data[1]!");

        });

        $form->setTitle("Edit a Floating-Text");
        $form->addLabel("Edit " . $floatingText->getName() . "'s content by filling out the form below.");
        $form->addInput("Text", "Text's content (use & for colors)", $floatingText->getText());

        $player->sendForm($form);
    }
}