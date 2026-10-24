<?php

declare(strict_types=1);

namespace App\Application\Planning\SupprimerSeance;

use App\Domain\Planning\Exceptions\ElementIntrouvableException;
use App\Domain\Planning\Repositories\SeanceRepository;

final class SupprimerSeance
{
    public function __construct(private readonly SeanceRepository $seances)
    {
    }

    public function execute(int $id): void
    {
        if ($this->seances->trouverParId($id) === null) {
            throw new ElementIntrouvableException('Séance introuvable.');
        }
        $this->seances->supprimer($id);
    }
}
