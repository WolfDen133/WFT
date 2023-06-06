<?php

namespace WolfDen133\WFT\Form\Types;

use pocketmine\player\Player;
use pocketmine\world\Position;
use WolfDen133\WFT\Texts\FloatingText;
use WolfDen133\WFT\WFT;
use Vecnavium\FormsUI\CustomForm;
use WolfDen133\WFT\Form\Form;

class CreationForm extends Form
{
    public const FORM_ID = 1;
    
    public function registerForm () : void
    {
        
    }
    
    public function sendTo(Player $player, FloatingText $floatingText = null): void
    {
        $form = new CustomForm(function (Player $player, array $data = null) : void { if (is_null($data)) { return; } $this->handleResponse($player, $data); });
        $form->setTitle(WFT::getInstance()->getLanguageManager()->getLanguage()->getFormText("create.title"));
        $form->addLabel(WFT::getInstance()->getLanguageManager()->getLanguage()->getFormText("create.content"));
        $form->addInput(WFT::getInstance()->getLanguageManager()->getLanguage()->getFormText("create.name-title"), WFT::getInstance()->getLanguageManager()->getLanguage()->getFormText("create.name-placeholder"));
        $form->addInput(WFT::getInstance()->getLanguageManager()->getLanguage()->getFormText("create.text-title"), WFT::getInstance()->getLanguageManager()->getLanguage()->getFormText("create.text-placeholder"));

        $player->sendForm($form);
    }
    
    public function handleResponse(Player $player, array|string|int $data): void
    {
        $this->registerForm();
        $api = WFT::getInstance()->getTextManager();

        if (in_array($data[1], array_keys($api->getTexts()))) {
            $player->sendMessage(WFT::getInstance()->getLanguageManager()->getLanguage()->getMessage("exists", ["{NAME}" => $data[1]]));
            return;
        }

        $floatingText = $api->registerText($data[1], $data[2], new Position($player->getPosition()->getX(), $player->getPosition()->getY() + 1.8, $player->getPosition()->getZ(), $player->getWorld()));

        $player->sendMessage(WFT::getInstance()->getLanguageManager()->getLanguage()->getMessage("add", ["{NAME}" => $floatingText->getName()]));
    }
}
