<?php

namespace WolfDen133\WFT\Form\Types;

use dktapps\pmforms\CustomForm;
use dktapps\pmforms\CustomFormResponse;
use dktapps\pmforms\element\Input;
use dktapps\pmforms\element\Label;
use dktapps\pmforms\element\Toggle;
use pocketmine\player\Player;
use pocketmine\world\Position;
use WolfDen133\WFT\Form\Form;
use WolfDen133\WFT\Texts\FloatingText;
use WolfDen133\WFT\WFT;

class CreationForm extends Form
{
    public const FORM_ID = 1;
    
    public function registerForm () : void
    {
        
    }
    
    public function sendTo(Player $player, FloatingText $floatingText = null): void
    {
        $form = new CustomForm(
            WFT::getInstance()->getLanguageManager()->getLanguage()->getFormText("create.title"),
            [
                new Label("content", WFT::getInstance()->getLanguageManager()->getLanguage()->getFormText("create.content")),
                new Input("uniqueName", WFT::getInstance()->getLanguageManager()->getLanguage()->getFormText("create.name-title"), WFT::getInstance()->getLanguageManager()->getLanguage()->getFormText("create.name-placeholder")),
                new Input("text", WFT::getInstance()->getLanguageManager()->getLanguage()->getFormText("create.text-title"), WFT::getInstance()->getLanguageManager()->getLanguage()->getFormText("create.text-placeholder")),
                new Toggle("isOp", "isOP")
            ], function (Player $player, CustomFormResponse $data) : void { if (is_null($data)) { return; } $this->handleResponse($player, $data); }
        );

        $player->sendForm($form);
    }
    
    public function handleResponse(Player $player, CustomFormResponse|int $data): void
    {
        $this->registerForm();
        $api = WFT::getInstance()->getTextManager();

        if (in_array($data->getString("uniqueName"), array_keys($api->getTexts()))) {
            $player->sendMessage(WFT::getInstance()->getLanguageManager()->getLanguage()->getMessage("exists", ["{NAME}" => $data->getString("uniqueName")]));
            return;
        }
        $floatingText = $api->registerText($data->getString("uniqueName"), $data->getString("text"), new Position($player->getPosition()->getX(), $player->getPosition()->getY() + 1.8, $player->getPosition()->getZ(), $player->getWorld()), true, true, $data->getBool("isOp"));

        $player->sendMessage(WFT::getInstance()->getLanguageManager()->getLanguage()->getMessage("add", ["{NAME}" => $floatingText->getName()]));
    }
}