<?php
namespace WolfDen133\WFT\Texts;

use pocketmine\entity\Entity;
use pocketmine\permission\DefaultPermissions;
use pocketmine\world\Position;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use Ramsey\Uuid\Uuid as UUID;
use WolfDen133\WFT\WFT;

class FloatingText
{
    private Position $position;

    private string $text;
    private string $name;

    /** @var SubText[] */
    public array $subtexts = [];
    public SubText $identifier;

    public function __construct(Position $position, string $name, string $text)
    {
        $this->position = $position;
        $this->text = $text;
        $this->name = strtolower($name);

        $this->registerSubTexts();

    }

    public function registerSubTexts () : void
    {
        $lines = explode("#", $this->text);
        $y = $this->getPosition()->getY();

        foreach ($this->subtexts as $subText) {
            foreach (WFT::getInstance()->getServer()->getOnlinePlayers() as $player) $subText->closeTo($player);
        }

        $this->subtexts = [];

        foreach ($lines as $line) {
            $subText = new SubText($line, new Position($this->getPosition()->getX(), $y, $this->getPosition()->getZ(), $this->getPosition()->getWorld()), UUID::uuid4(), Entity::nextRuntimeId());

            $this->subtexts[] = $subText;
            $y = $y - 0.3;
        }

        $this->identifier = new SubText(TextFormat::DARK_GRAY . "[" . $this->getName() . "]", new Position($this->position->getX(), $y, $this->position->getZ(), $this->position->getWorld()), UUID::uuid4(), Entity::nextRuntimeId());
    }

    /**
     * @return Position
     */
    public function getPosition(): Position
    {
        return $this->position;
    }

    /**
     * @return string
     */
    public function getText(): string
    {
        return $this->text;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    public function setText (string $raw) : void
    {
        $this->text = $raw;
    }

    /**
     * Used for updating tags
     *
     * @param Player $player
     */
    public function updateTextTo (Player $player) : void
    {
        foreach ($this->subtexts as $subText) {
            $subText->updateTextTo($player);
        }
    }

    public function setPosition (Position $position) : void
    {
        $this->position = $position;
    }

    public function spawnTo (Player $player) : void
    {
        foreach ($this->subtexts as $subText) {
            $subText->spawnTo($player);
        }

        if (WFT::$display_identifier) {
            if ($player->hasPermission(DefaultPermissions::ROOT_OPERATOR)) $this->identifier->spawnTo($player);
        }
    }

    public function closeTo (Player $player) : void
    {
        foreach ($this->subtexts as $subText) {
            $subText->closeTo($player);
        }

        $this->identifier->closeTo($player);
    }

    public function respawnTo (Player $player) : void
    {
        $this->closeTo($player);

        $this->registerSubTexts();

        if (WFT::$display_identifier) {
            if ($player->hasPermission(DefaultPermissions::ROOT_OPERATOR)) $this->identifier->spawnTo($player);
        }

        $this->spawnTo($player);
    }

}