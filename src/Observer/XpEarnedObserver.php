<?php

namespace App\Observer;

use App\FightResult;
use App\Observer\GameObserverInterface;
use App\Service\XpCalculator;

class XpEarnedObserver implements GameObserverInterface
{

    public function __construct(private readonly XpCalculator $xpCalculator)
    {
    }

    public function onFightFinished(FightResult $fightResult): void
    {
        $winner = $fightResult->getWinner();
        $loser = $fightResult->getLoser();

        $this->xpCalculator->addXp($winner, $loser->getLevel());
    }
}