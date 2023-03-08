<?php

namespace WolfDen133\WFT\Form;

use pocketmine\player\Player;
use WolfDen133\WFT\Form\Types\CreationForm;
use WolfDen133\WFT\Form\Types\EditForm;
use WolfDen133\WFT\Form\Types\ListForm;
use WolfDen133\WFT\Texts\FloatingText;

class FormManager
{
    /** @var Form[] */
    private array $forms = [];

    public function __construct()
    {
        $this->registerForms();
    }

    private function registerForms () : void
    {
        $this->registerForm(CreationForm::FORM_ID, new CreationForm());
        $this->registerForm(EditForm::FORM_ID, new EditForm());
        $this->registerForm(ListForm::FORM_ID, new ListForm());
    }

    private function registerForm (int $id, Form $form) : void
    {
        $this->forms[$id] = $form;
    }

    public function sendFormTo (Player $player, int $type, int $arg = 0, FloatingText $text = null) : void
    {
        if ($type == ListForm::FORM_ID) $this->forms[$type]->setMode($arg);

        $this->forms[$type]->sendTo($player, $text);
    }
}