<?php

namespace WolfDen133\WFT\Command;

use pocketmine\command\Command;

use pocketmine\plugin\Plugin;
use pocketmine\plugin\PluginOwned;

use WolfDen133\WFT\Form\CustomForm;
use WolfDen133\WFT\Form\SimpleForm;

use pocketmine\command\CommandSender;

use pocketmine\world\Position;

use pocketmine\player\Player;

use pocketmine\utils\TextFormat;

use WolfDen133\WFT\WFT;
use WolfDen133\WFT\Texts\FloatingText;

class WFTCommand extends Command implements PluginOwned
{
    public const MODE_EDIT = 0;
    public const MODE_TP = 1;
    public const MODE_TPHERE = 2;
    public const MODE_REMOVE = 3;

    public function __construct(string $name)
    {
        parent::__construct($name);

        $this->setPermission(WFT::getLanguageManager()->getLanguage()->getValue("command.permission"));
        $this->setDescription(WFT::getLanguageManager()->getLanguage()->getValue("command.description"));
        $this->setAliases(WFT::getLanguageManager()->getLanguage()->getValue("command.aliases"));
        $this->setUsage("/wft help");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args)
    {
        if (!($sender instanceof Player)) {
            $sender->sendMessage(WFT::getLanguageManager()->getLanguage()->getValue("command.sender"));
            return;
        }

        if (!$this->testPermission($sender)) return;

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
                        " Level: " . $text->getPosition()->getWorld()->getDisplayName() . "\n" .
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

                    $sender->sendMessage(str_replace("{LINE}", "\n", WFT::getLanguageManager()->getLanguage()->getValue("command.help")));
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
                default:
                    $sender->sendMessage($this->getUsage());
                    return;
            }

            return;
        }

        if (count($args) == 2) {

            if (($text = WFT::getAPI()->getTextByName($args[1])) === null) {
                $sender->sendMessage(WFT::getLanguageManager()->getLanguage()->getMessage("not-found", ["{NAME}" => $args[1]]));
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
                    $sender->sendMessage(WFT::getLanguageManager()->getLanguage()->getMessage("remove", ["{NAME}" => $text->getName()]));

                    break;
                case "tp":
                case "teleportto":
                case "tpto":
                case "goto":
                case "teleport":

                    WFT::getInstance()->levelCheck($text->getPosition()->getWorld()->getDisplayName());
                    $sender->teleport($text->getPosition());

                    break;
                case "tphere":
                case "teleporthere":
                case "movehere":
                case "bringhere":
                case "tph":
                case "move":

                    $text->setPosition(new Position($sender->getPosition()->getX(), $sender->getPosition()->getY() + 1.8, $sender->getPosition()->getZ(), $sender->getWorld()));
                    WFT::getAPI()::respawnToAll($text);
                    WFT::getAPI()->generateConfig($text);

                    break;
                default:
                    $sender->sendMessage($this->getUsage());
                    return;
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
                        $sender->sendMessage(WFT::getLanguageManager()->getLanguage()->getMessage("exists", ["{NAME}" => $args[1]]));
                        return;
                    }

                    $floatingText = new FloatingText(new Position($sender->getPosition()->getX(), $sender->getPosition()->getY() + 1.8, $sender->getPosition()->getZ(), $sender->getWorld()), $args[1], implode(" ", array_splice($args, 2)));

                    $api->registerText($floatingText);
                    $api->generateConfig($floatingText);
                    $api::spawnToAll($floatingText);

                    $sender->sendMessage(WFT::getLanguageManager()->getLanguage()->getMessage("add", ["{NAME}" => $floatingText->getName()]));
                    break;
                case "edit":
                case "e":
                case "change":

                    if (($text = WFT::getAPI()->getTextByName($args[1])) === null) {
                        $sender->sendMessage(WFT::getLanguageManager()->getLanguage()->getMessage("not-found", ["{NAME}" => $args[1]]));
                        return;
                    }

                    $text->setText(implode(" ", array_splice($args, 2)));
                    $api->generateConfig($text);
                    $api::respawnToAll($text);
                    $sender->sendMessage(WFT::getLanguageManager()->getLanguage()->getMessage("update", ["{NAME}" => $args[1]]));
                    break;
                default:
                    $sender->sendMessage($this->getUsage());
                    return;
            }
            return;
        }

        $sender->sendMessage($this->getUsage());
    }

    public function openCreationForm (Player $player) : void
    {
        $form = new CustomForm(function (Player $player, array $data = null) : void
        {
            if (is_null($data)) return;

            $api = WFT::getAPI();

            if (in_array($data[1], array_keys($api->getTexts()))) {
                $player->sendMessage(WFT::getLanguageManager()->getLanguage()->getMessage("exists", ["{NAME}" => $data[1]]));
                return;
            }

            $floatingText = new FloatingText(new Position($player->getPosition()->getX(), $player->getPosition()->getY() + 1.8, $player->getPosition()->getZ(), $player->getWorld()), $data[1], $data[2]);

            $api->registerText($floatingText);
            $api->generateConfig($floatingText);
            $api::spawnToAll($floatingText);

            $player->sendMessage(WFT::getLanguageManager()->getLanguage()->getMessage("add", ["{NAME}" => $floatingText->getName()]));
        });

        $form->setTitle(WFT::getLanguageManager()->getLanguage()->getFormText("create.title"));
        $form->addLabel(WFT::getLanguageManager()->getLanguage()->getFormText("create.content"));
        $form->addInput(WFT::getLanguageManager()->getLanguage()->getFormText("create.name-title"), WFT::getLanguageManager()->getLanguage()->getFormText("create.name-placeholder"));
        $form->addInput(WFT::getLanguageManager()->getLanguage()->getFormText("create.text-title"), WFT::getLanguageManager()->getLanguage()->getFormText("create.text-placeholder"));

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

                    WFT::getInstance()->levelCheck($text->getPosition()->getWorld()->getDisplayName());
                    $player->teleport($text->getPosition());

                    break;
                case self::MODE_TPHERE:

                    $text->setPosition(new Position($player->getPosition()->getX(), $player->getPosition()->getY() + 1.8, $player->getPosition()->getZ(), $player->getWorld()));
                    WFT::getAPI()::respawnToAll($text);
                    WFT::getAPI()->generateConfig($text);

                    break;
                case self::MODE_REMOVE:

                    WFT::getAPI()->removeText($text);
                    $player->sendMessage(WFT::getLanguageManager()->getLanguage()->getMessage("remove", ["{NAME}" => $text->getName()]));

                    break;
            }
        });

        $form->setTitle(WFT::getLanguageManager()->getLanguage()->getFormText("list-title"));

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
            $api->generateConfig($floatingText);
            $api::respawnToAll($floatingText);
            $player->sendMessage(WFT::getLanguageManager()->getLanguage()->getMessage("update", ["{NAME}" => $floatingText->getName()]));

        });

        $form->setTitle(WFT::getLanguageManager()->getLanguage()->getFormText("edit.title"));
        $form->addLabel(WFT::getLanguageManager()->getLanguage()->getFormText("edit.content", ["{NAME}" => $floatingText->getName()]));
        $form->addInput(WFT::getLanguageManager()->getLanguage()->getFormText("edit.text-title"), WFT::getLanguageManager()->getLanguage()->getFormText("edit.text-placeholder"), $floatingText->getText());

        $player->sendForm($form);
    }

    public function getOwningPlugin(): Plugin
    {
        return WFT::getInstance();
    }
}
