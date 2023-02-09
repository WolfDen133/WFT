<?php

namespace WolfDen133\WFT\Texts;

use pocketmine\network\mcpe\protocol\RemoveActorPacket;;
use pocketmine\network\mcpe\protocol\SetActorDataPacket;
use pocketmine\network\mcpe\protocol\types\AbilitiesData;
use pocketmine\network\mcpe\protocol\types\AbilitiesLayer;
use pocketmine\network\mcpe\protocol\types\command\CommandPermissions;
use pocketmine\network\mcpe\protocol\types\DeviceOS;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataFlags;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataProperties;
use pocketmine\network\mcpe\protocol\types\entity\FloatMetadataProperty;
use pocketmine\network\mcpe\protocol\types\entity\LongMetadataProperty;
use pocketmine\network\mcpe\protocol\types\entity\PropertySyncData;
use pocketmine\network\mcpe\protocol\types\entity\StringMetadataProperty;
use pocketmine\network\mcpe\protocol\types\inventory\ItemStack;
use pocketmine\network\mcpe\protocol\types\PlayerPermissions;
use pocketmine\network\mcpe\protocol\UpdateAbilitiesPacket;
use pocketmine\player\GameMode;
use Ramsey\Uuid\Uuid as UUID;
use pocketmine\entity\Skin;
use pocketmine\world\Position;
use pocketmine\network\mcpe\protocol\AddPlayerPacket;
use pocketmine\network\mcpe\protocol\DataPacket;
use pocketmine\network\mcpe\protocol\PlayerListPacket;
use pocketmine\network\mcpe\protocol\types\inventory\ItemStackWrapper;
use pocketmine\network\mcpe\protocol\types\PlayerListEntry;
use pocketmine\network\mcpe\convert\SkinAdapterSingleton;
use WolfDen133\WFT\Utils\Utils;
use pocketmine\player\Player;

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
        $pk = SetActorDataPacket::create($this->runtime,
            [ EntityMetadataProperties::NAMETAG => new StringMetadataProperty(Utils::getFormattedText($this->text, $player)) ],
            new PropertySyncData([], []),
            0
        );

        $player->getNetworkSession()->sendDataPacket($pk);
    }

    public function spawnTo (Player $player) : void
    {
        /** @var DataPacket $pks */
        $pks = [];

        $pks[] = PlayerListPacket::add([PlayerListEntry::createAdditionEntry(
            UUID::fromString($this->uuid),
            $this->runtime,
            "",
            SkinAdapterSingleton::get()->toSkinData(new Skin(
                "Standard_Custom",
                str_repeat("\x00", 8192)
            ))
        )]);

        $pks[] = AddPlayerPacket::create(
            UUID::fromString($this->uuid),
            $this->text,
            $this->runtime,
            $this->runtime,
            $this->position->asVector3(),
            null,
            0,
            0,
            0,
            ItemStackWrapper::legacy(ItemStack::null()),
            1,
            [
                EntityMetadataProperties::FLAGS => new LongMetadataProperty(1 << EntityMetadataFlags::IMMOBILE),
                EntityMetadataProperties::SCALE => new FloatMetadataProperty(0)
            ],
            new PropertySyncData([], []),
            UpdateAbilitiesPacket::create(new AbilitiesData(CommandPermissions::NORMAL, PlayerPermissions::CUSTOM, $this->runtime, [
                new AbilitiesLayer(
                    AbilitiesLayer::LAYER_BASE,
                    array_fill(0, AbilitiesLayer::NUMBER_OF_ABILITIES, false),
                    0.0,
                    0.0
                )
            ])),
            [],
            "",
            DeviceOS::UNKNOWN
        );

        $pks[] = PlayerListPacket::remove([PlayerListEntry::createRemovalEntry(UUID::fromString($this->uuid))]);

        foreach ($pks as $pk) $player->getNetworkSession()->sendDataPacket($pk);

    }

    public function closeTo (Player $player) : void
    {
        $pk = RemoveActorPacket::create($this->runtime);

        $player->getNetworkSession()->sendDataPacket($pk);
    }
}
