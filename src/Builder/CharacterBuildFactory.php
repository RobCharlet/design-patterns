<?php

namespace App\Builder;

use Psr\Log\LoggerInterface;

class CharacterBuildFactory
{
    public function __construct(private LoggerInterface $logger)
    {
    }

    public function createBuilder(): CharacterBuilder
    {
        return new CharacterBuilder($this->logger);
    }
}