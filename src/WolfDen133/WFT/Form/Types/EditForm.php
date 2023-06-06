<?php

namespace WolfDen133\WFT\Form\Types;

use WolfDen133\WFT\lib\FormAPI\CustomForm;
use pocketmine\player\Player;
use WolfDen133\WFT\Texts\FloatingText;
use WolfDen133\WFT\WFT;
use WolfDen133\WFT\Form\Form;

class EditForm extends Form
{
    public const FORM_ID = 3;

    private FloatingText $floatingText;

    public function sendTo(Player $player, FloatingText $floatingText = null): void
    {
        $this->floatingText = $floatingText;
        $form = new CustomForm(function (Player $player, array $data = null) : void { if (is_null($data)) { return; } $this->handleResponse($player, $data); });

        $form->setTitle(WFT::getInstance()->getLanguageManager()->getLanguage()->getFormText("edit.title"));
        $form->addLabel(WFT::getInstance()->getLanguageManager()->getLanguage()->getFormText("edit.content", ["{NAME}" => $floatingText->getName()]));
        $form->addInput(WFT::getInstance()->getLanguageManager()->getLanguage()->getFormText("edit.text-title"), WFT::getInstance()->getLanguageManager()->getLanguage()->getFormText("edit.text-placeholder"), $floatingText->getText());

        $player->sendForm($form);
    }

    public function handleResponse(Player $player, array|string|int $data): void
    {
        $this->floatingText->setText($data[1]);
        WFT::getInstance()->getTextManager()->saveText($this->floatingText);
        WFT::getInstance()->getTextManager()->getActions()->respawnToAll($this->floatingText->getName());
        $player->sendMessage(WFT::getInstance()->getLanguageManager()->getLanguage()->getMessage("update", ["{NAME}" => $this->floatingText->getName()]));
    }
}