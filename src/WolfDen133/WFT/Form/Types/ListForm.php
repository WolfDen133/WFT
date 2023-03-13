<?php

namespace WolfDen133\WFT\Form\Types;

use jojoe77777\FormAPI\SimpleForm;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use pocketmine\world\Position;
use WolfDen133\WFT\Texts\FloatingText;
use WolfDen133\WFT\WFT;
use WolfDen133\WFT\Form\Form;

class ListForm extends Form
{
    public const FORM_ID = 4;

    public const MODE_EDIT = 0;
    public const MODE_TP = 1;
    public const MODE_TPHERE = 2;
    public const MODE_REMOVE = 3;

    private int $mode = 0;

    public function setMode (int $mode) : void
    {
        $this->mode = $mode;
    }

    public function sendTo(Player $player, FloatingText $floatingText = null): void
    {
        $form = new SimpleForm(function (Player $player, string $data = null) : void { if (is_null($data)) { return; } $this->handleResponse($player, $data); });

        $form->setTitle(WFT::getInstance()->getLanguageManager()->getLanguage()->getFormText("list-title"));

        foreach (WFT::getInstance()->getTextManager()->getTexts() as $text) {
            $form->addButton(TextFormat::DARK_GRAY . $text->getName() . "\n" . TextFormat::GRAY . $text->getText(), -1, "", $text->getName());
        }

        $player->sendForm($form);
    }

    public function handleResponse(Player $player, array|string|int $data): void
    {
        $text = WFT::getInstance()->getTextManager()->getTextById($data);

        switch ($this->mode) {
            case self::MODE_EDIT:
                WFT::getInstance()->getFormManager()->sendFormTo($player, EditForm::FORM_ID, 0, $text);
                break;
            case self::MODE_TP:

                WFT::getInstance()->getTextManager()->levelCheck($text->getPosition()->getWorld()->getDisplayName());
                $player->teleport($text->getPosition());

                break;
            case self::MODE_TPHERE:

                $text->setPosition(new Position($player->getPosition()->getX(), $player->getPosition()->getY() + 1.8, $player->getPosition()->getZ(), $player->getWorld()));
                WFT::getInstance()->getTextManager()->getActions()->respawnToAll($text->getName());
                WFT::getInstance()->getTextManager()->saveText($text);

                break;
            case self::MODE_REMOVE:

                WFT::getInstance()->getTextManager()->removeText($text->getName());
                $player->sendMessage(WFT::getInstance()->getLanguageManager()->getLanguage()->getMessage("remove", ["{NAME}" => $text->getName()]));

                break;
        }
    }
}