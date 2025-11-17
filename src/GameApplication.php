<?php

namespace App;


use App\Builder\CharacterBuilder;
use App\Builder\CharacterBuildFactory;
use App\Character\Character;
use App\Enum\ArmorTypeEnum;
use App\Enum\AttackTypeEnum;
use App\Observer\GameObserverInterface;

class GameApplication
{
    /** @var GameObserverInterface[] */
    private array $observers = [];

    public function __construct(private CharacterBuildFactory $characterBuildFactory)
    {
    }

    public function play(Character $player, Character $ai): FightResult
    {
        $player->rest();

        $fightResult = new FightResult();
        while (true) {
            $fightResult->addRound();

            $damage = $player->attack();
            if ($damage === 0) {
                $fightResult->addExhaustedTurn();
            }

            $damageDealt = $ai->receiveAttack($damage);
            $fightResult->addDamageDealt($damageDealt);

            if ($this->didPlayerDie($ai)) {
                return $this->finishFightResult($fightResult, $player, $ai);
            }

            $damageReceived = $player->receiveAttack($ai->attack());
            $fightResult->addDamageReceived($damageReceived);

            if ($this->didPlayerDie($player)) {
                return $this->finishFightResult($fightResult, $ai, $player);
            }
        }
    }

    public function createCharacter(string $character): Character
    {
        return match (strtolower($character)) {
            'fighter' => $this->createCharacterBuilder()
                ->setMaxHeath(90)
                ->setBaseDamage(15)
                ->setAttackType(AttackTypeEnum::SWORD)
                ->setArmorType(ArmorTypeEnum::SHIELD)
                ->buildCharacter()
            ,
            'archer' => $this->createCharacterBuilder()
                ->setMaxHeath(80)
                ->setBaseDamage(10)
                ->setAttackType(AttackTypeEnum::BOW)
                ->setArmorType(ArmorTypeEnum::LEATHER_ARMOR)
                ->buildCharacter()
            ,
            'mage' => $this->createCharacterBuilder()
                ->setMaxHeath(70)
                ->setBaseDamage(8)
                ->setAttackType(AttackTypeEnum::FIREBOLT)
                ->setArmorType(ArmorTypeEnum::ICE_BLOCK)
                ->buildCharacter()
            ,
            'mage_archer' => $this->createCharacterBuilder()
                ->setMaxHeath(75)
                ->setBaseDamage(9)
                ->setAttackType(AttackTypeEnum::BOW, AttackTypeEnum::FIREBOLT) //TODO
                ->setArmorType(ArmorTypeEnum::SHIELD)
                ->buildCharacter()
            ,
            default => throw new \RuntimeException('Undefined Character'),
        };
    }

    public function getCharactersList(): array
    {
        return [
            'fighter',
            'mage',
            'archer',
            'mage_archer',
        ];
    }

    public function subscribe(GameObserverInterface $gameObserver): void
    {
        if (!in_array($gameObserver, $this->observers)) {
            $this->observers[] = $gameObserver;
        }
    }

    public function unSubscribe(GameObserverInterface $gameObserver): void
    {
        $key = array_search($gameObserver, $this->observers, true);
        if ($key !== false) {
            unset($this->observers[$key]);
        }
    }

    private function finishFightResult(FightResult $fightResult, Character $winner, Character $loser): FightResult
    {
        $fightResult->setWinner($winner);
        $fightResult->setLoser($loser);

        $this->notify($fightResult);

        return $fightResult;
    }

    private function didPlayerDie(Character $player): bool
    {
        return $player->getCurrentHealth() <= 0;
    }

    private function createCharacterBuilder(): CharacterBuilder
    {
        return $this->characterBuildFactory->createBuilder();
    }

    private function notify(FightResult $fightResult): void
    {
        foreach ($this->observers as $observer) {
            $observer->onFightFinished($fightResult);
        }
    }
}
