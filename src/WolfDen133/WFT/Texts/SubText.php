<?php

namespace WolfDen133\WFT\Texts;

use pocketmine\network\mcpe\protocol\RemoveActorPacket;
use pocketmine\network\mcpe\protocol\SetActorDataPacket;
use pocketmine\utils\UUID;
use pocketmine\block\BlockIds;
use pocketmine\entity\Entity;
use pocketmine\entity\Skin;
use pocketmine\item\Item;
use pocketmine\level\Position;
use pocketmine\network\mcpe\protocol\AddPlayerPacket;
use pocketmine\network\mcpe\protocol\DataPacket;
use pocketmine\network\mcpe\protocol\PlayerListPacket;
use pocketmine\network\mcpe\protocol\types\inventory\ItemStackWrapper;
use pocketmine\network\mcpe\protocol\types\PlayerListEntry;
use pocketmine\network\mcpe\protocol\types\SkinAdapterSingleton;
use pocketmine\Player;
use WolfDen133\WFT\Utils\Utils;

class SubText
{
    private string $text;
    private Position $position;

    private string $uuid;
    private int $runtime;

    public function __construct(string $text, Position $position, string $uuid, int $runtimeID)
    {
        $this->text = $text;
        $this->position = $position;
        $this->runtime = $runtimeID;
        $this->uuid = $uuid;
    }

    public function setText (string $text) : void
    {
        $this->text = $text;
    }

    public function updateTextTo (Player $player) : void
    {
        $pk = new SetActorDataPacket();
        $pk->entityRuntimeId = $this->runtime;
        $pk->metadata = [
            Entity::DATA_NAMETAG => [
                Entity::DATA_NAMETAG, Utils::getFormattedText($this->text, $player)
            ]
        ];

        $player->sendDataPacket($pk);
    }

    public function spawnTo (Player $player) : void
    {
        /** @var DataPacket $pks */
        $pks = [];

        $pk = new PlayerListPacket();
        $pk->type = PlayerListPacket::TYPE_ADD;
        $pk->entries = [
            PlayerListEntry::createAdditionEntry(
                UUID::fromData($this->uuid),
                $this->runtime,
                "",
                SkinAdapterSingleton::get()->toSkinData(new Skin(
                    "Standard_Custom",
                    str_repeat("\x00", 8192)
                ))
            )
        ];
        $pks[] = $pk;

        $pk = new AddPlayerPacket();
        $pk->item = ItemStackWrapper::legacy(Item::get(BlockIds::AIR));
        $pk->uuid = UUID::fromString($this->uuid);
        $pk->entityRuntimeId = $this->runtime;
        $pk->username = Utils::getFormattedText($this->text, $player);
        $pk->position = $this->position->asVector3();
        $pk->metadata = [
            Entity::DATA_FLAGS => [
                Entity::DATA_TYPE_LONG, 1 << Entity::DATA_FLAG_IMMOBILE
            ],
            Entity::DATA_SCALE => [
                Entity::DATA_TYPE_FLOAT, 0
            ]
        ];
        $pks[] = $pk;

        $pk = new PlayerListPacket();
        $pk->type = PlayerListPacket::TYPE_REMOVE;
        $pk->entries = [PlayerListEntry::createRemovalEntry(UUID::fromData($this->uuid))];

        $pks[] = $pk;

        foreach ($pks as $pk) $player->sendDataPacket($pk);

    }

    public function closeTo (Player $player) : void
    {
        $pk = new RemoveActorPacket();
        $pk->entityUniqueId = $this->runtime;

        $player->sendDataPacket($pk);
    }
}