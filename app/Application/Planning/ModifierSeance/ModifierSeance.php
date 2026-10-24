<?php

declare(strict_types=1);

namespace App\Application\Planning\ModifierSeance;

use App\Application\Planning\DonneesSeance;
use App\Application\Planning\ValidateurSeance;
use App\Domain\Planning\Entities\Seance;
use App\Domain\Planning\Exceptions\ElementIntrouvableException;
use App\Domain\Planning\Repositories\SeanceRepository;

final class ModifierSeance
{
    public function __construct(
        private readonly SeanceRepository $seances,
        private readonly ValidateurSeance $validateur,
    ) {
    }

    public function execute(int $id, DonneesSeance $donnees): Seance
    {
        if ($this->seances->trouverParId($id) === null) {
            throw new ElementIntrouvableException('Séance introuvable.');
        }
        $this->validateur->valider($donnees);

        return $this->seances->modifier($id, $donnees->versTableau());
    }
}
