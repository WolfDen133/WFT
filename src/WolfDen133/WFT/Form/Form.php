<?php

namespace WolfDen133\WFT\Form;

use pocketmine\player\Player;
use WolfDen133\WFT\Texts\FloatingText;
use jojoe77777\FormAPI\SimpleForm;
use jojoe77777\FormAPI\CustomForm;

class Form
{
    public function sendTo (Player $player, FloatingText $floatingText = null) : void
    {}
    public function handleResponse(Player $player, array|string|int $data): void {}

}