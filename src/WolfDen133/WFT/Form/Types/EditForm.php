<?php

namespace WolfDen133\WFT\Form\Types;

use dktapps\pmforms\CustomForm;
use dktapps\pmforms\CustomFormResponse;
use dktapps\pmforms\element\Input;
use dktapps\pmforms\element\Label;
use pocketmine\player\Player;
use WolfDen133\WFT\Form\Form;
use WolfDen133\WFT\Texts\FloatingText;
use WolfDen133\WFT\WFT;

class EditForm extends Form
{
    public const FORM_ID = 3;

    private FloatingText $floatingText;

    public function sendTo(Player $player, FloatingText $floatingText = null): void
    {
        $this->floatingText = $floatingText;
        $form = new CustomForm(
            WFT::getInstance()->getLanguageManager()->getLanguage()->getFormText("edit.title"), [
            new Label("content", WFT::getInstance()->getLanguageManager()->getLanguage()->getFormText("edit.content", ["{NAME}" => $floatingText->getName()])),
            new Input("text", WFT::getInstance()->getLanguageManager()->getLanguage()->getFormText("edit.text-title"), WFT::getInstance()->getLanguageManager()->getLanguage()->getFormText("edit.text-placeholder"), $floatingText->getText())
        ],
            function (Player $player, CustomFormResponse $data = null) : void { if (is_null($data)) { return; } $this->handleResponse($player, $data); }
        );


        $player->sendForm($form);
    }

    public function handleResponse(Player $player, CustomFormResponse|int $data): void
    {
        $this->floatingText->setText($data->getString("text"));
        WFT::getInstance()->getTextManager()->saveText($this->floatingText);
        WFT::getInstance()->getTextManager()->getActions()->respawnToAll($this->floatingText->getName());
        $player->sendMessage(WFT::getInstance()->getLanguageManager()->getLanguage()->getMessage("update", ["{NAME}" => $this->floatingText->getName()]));
    }
}