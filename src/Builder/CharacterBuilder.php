<?php

namespace App\Builder;

use App\ArmorType\ArmorType;
use App\ArmorType\IceBlockType;
use App\ArmorType\LeatherArmorType;
use App\ArmorType\ShieldType;
use App\AttackType\AttackType;
use App\AttackType\BowType;
use App\AttackType\FireBoltType;
use App\AttackType\MultiAttackType;
use App\AttackType\TwoHandedSwordType;
use App\Character\Character;
use App\Enum\ArmorTypeEnum;
use App\Enum\AttackTypeEnum;
use Psr\Log\LoggerInterface;

class CharacterBuilder
{
    private int $maxHealth;
    private int $baseDamage;
    private array $attackTypes;
    private ArmorTypeEnum $armorType;


    public function __construct(private LoggerInterface $logger)
    {
    }

    public function setMaxHeath(int $maxHealth): self
    {
        $this->maxHealth = $maxHealth;

        return $this;
    }

    public function setBaseDamage(int $baseDamage): self
    {
        $this->baseDamage = $baseDamage;

        return $this;
    }

    public function setAttackType(AttackTypeEnum ...$attackTypes): self
    {

        $this->attackTypes = $attackTypes;

        return $this;
    }

    public function setArmorType(ArmorTypeEnum $armorType): self
    {
        $this->armorType = $armorType;

        return $this;
    }

    public function buildCharacter(): Character
    {
        $this->logger->info('Creating a character', [
            'maxHealth' => $this->maxHealth,
            'baseDamage' => $this->baseDamage,
        ]);

        $attackTypes = array_map(fn(AttackTypeEnum $attackType) => $this->createAttackType($attackType), $this->attackTypes);

        if (count($attackTypes) === 1) {
            $attackType = $attackTypes[0];
        } else {
            $attackType = new MultiAttackType($attackTypes);
        }

        return new Character(
            $this->maxHealth,
            $this->baseDamage,
            $attackType,
            $this->createArmorType()
        );
    }

    private function createAttackType(AttackTypeEnum $attackType): AttackType
    {
        return match ($attackType) {
            AttackTypeEnum::BOW => new BowType(),
            AttackTypeEnum::FIREBOLT => new FireBoltType(),
            AttackTypeEnum::SWORD => new TwoHandedSwordType(),
        };
    }

    private function createArmorType(): ArmorType
    {
        return match ($this->armorType) {
            ArmorTypeEnum::ICE_BLOCK => new IceBlockType(),
            ArmorTypeEnum::SHIELD => new ShieldType(),
            ArmorTypeEnum::LEATHER_ARMOR => new LeatherArmorType(),
        };
    }
}