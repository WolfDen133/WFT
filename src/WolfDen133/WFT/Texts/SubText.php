<?php

namespace WolfDen133\WFT\Texts;

use pocketmine\block\VanillaBlocks;
use pocketmine\entity\Skin;
use pocketmine\network\mcpe\convert\TypeConverter;
use pocketmine\network\mcpe\protocol\AddActorPacket;
use pocketmine\network\mcpe\protocol\AddPlayerPacket;
use pocketmine\network\mcpe\protocol\DataPacket;
use pocketmine\network\mcpe\protocol\PlayerListPacket;
use pocketmine\network\mcpe\protocol\RemoveActorPacket;
use pocketmine\network\mcpe\protocol\SetActorDataPacket;
use pocketmine\network\mcpe\protocol\types\AbilitiesData;
use pocketmine\network\mcpe\protocol\types\AbilitiesLayer;
use pocketmine\network\mcpe\protocol\types\command\CommandPermissions;
use pocketmine\network\mcpe\protocol\types\DeviceOS;
use pocketmine\network\mcpe\protocol\types\entity\ByteMetadataProperty;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataFlags;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataProperties;
use pocketmine\network\mcpe\protocol\types\entity\FloatMetadataProperty;
use pocketmine\network\mcpe\protocol\types\entity\IntMetadataProperty;
use pocketmine\network\mcpe\protocol\types\entity\LongMetadataProperty;
use pocketmine\network\mcpe\protocol\types\entity\PropertySyncData;
use pocketmine\network\mcpe\protocol\types\entity\StringMetadataProperty;
use pocketmine\network\mcpe\protocol\types\inventory\ItemStack;
use pocketmine\network\mcpe\protocol\types\inventory\ItemStackWrapper;
use pocketmine\network\mcpe\protocol\types\PlayerListEntry;
use pocketmine\network\mcpe\protocol\types\PlayerPermissions;
use pocketmine\network\mcpe\protocol\UpdateAbilitiesPacket;
use pocketmine\player\Player;
use pocketmine\world\Position;
use Ramsey\Uuid\Uuid as UUID;
use WolfDen133\WFT\Utils\Utils;

;

class SubText
{
    private string $text;
    private Position $position;
    private int $runtime;

    public function __construct(string $text, Position $position, int $runtimeID)
    {
        $this->text = $text;
        $this->position = $position;
        $this->runtime = $runtimeID;
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
        $player->getNetworkSession()->sendDataPacket(AddActorPacket::create(
            $this->runtime,
            $this->runtime,
            EntityIds::FALLING_BLOCK,
            $this->position->asVector3(),
            null,
            0,
            0,
            0,
            0,
            [],
            [
                EntityMetadataProperties::FLAGS => new LongMetadataProperty(1 << EntityMetadataFlags::IMMOBILE),
                EntityMetadataProperties::SCALE => new FloatMetadataProperty(0.01),
                EntityMetadataProperties::BOUNDING_BOX_WIDTH => new FloatMetadataProperty(0.0),
                EntityMetadataProperties::BOUNDING_BOX_HEIGHT => new FloatMetadataProperty(0.0),
                EntityMetadataProperties::NAMETAG => new StringMetadataProperty($this->text),
                EntityMetadataProperties::VARIANT => new IntMetadataProperty(TypeConverter::getInstance()->getBlockTranslator()->internalIdToNetworkId(VanillaBlocks::AIR()->getStateId())),
                EntityMetadataProperties::ALWAYS_SHOW_NAMETAG => new ByteMetadataProperty(1),
            ],
            new PropertySyncData([], []),
            []
        ));
    }

    public function closeTo (Player $player) : void
    {
        $pk = RemoveActorPacket::create($this->runtime);

        $player->getNetworkSession()->sendDataPacket($pk);
    }
}
