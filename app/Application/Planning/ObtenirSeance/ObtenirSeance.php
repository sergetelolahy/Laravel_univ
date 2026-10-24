<?php

declare(strict_types=1);

namespace App\Application\Planning\ObtenirSeance;

use App\Domain\Planning\Entities\Seance;
use App\Domain\Planning\Exceptions\ElementIntrouvableException;
use App\Domain\Planning\Repositories\SeanceRepository;

final class ObtenirSeance
{
    public function __construct(private readonly SeanceRepository $seances)
    {
    }

    public function execute(int $id): Seance
    {
        return $this->seances->trouverParId($id)
            ?? throw new ElementIntrouvableException('Séance introuvable.');
    }
}
