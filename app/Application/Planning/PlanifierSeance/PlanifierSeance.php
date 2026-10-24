<?php

declare(strict_types=1);

namespace App\Application\Planning\PlanifierSeance;

use App\Application\Planning\DonneesSeance;
use App\Application\Planning\ValidateurSeance;
use App\Domain\Planning\Entities\Seance;
use App\Domain\Planning\Repositories\SeanceRepository;

final class PlanifierSeance
{
    public function __construct(
        private readonly SeanceRepository $seances,
        private readonly ValidateurSeance $validateur,
    ) {
    }

    public function execute(DonneesSeance $donnees): Seance
    {
        $this->validateur->valider($donnees);

        return $this->seances->creer($donnees->versTableau());
    }
}
