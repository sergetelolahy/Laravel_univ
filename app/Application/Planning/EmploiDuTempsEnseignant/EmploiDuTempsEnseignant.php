<?php

declare(strict_types=1);

namespace App\Application\Planning\EmploiDuTempsEnseignant;

use App\Application\Planning\Periode;
use App\Domain\Planning\Entities\Seance;
use App\Domain\Planning\Exceptions\ElementIntrouvableException;
use App\Domain\Planning\Repositories\PlanningContexte;
use App\Domain\Planning\Repositories\SeanceRepository;

final class EmploiDuTempsEnseignant
{
    public function __construct(
        private readonly SeanceRepository $seances,
        private readonly PlanningContexte $contexte,
    ) {
    }

    /** @return array{du:string,au:string,seances:Seance[]} */
    public function execute(int $utilisateurId, ?string $du, ?string $au): array
    {
        $enseignantId = $this->contexte->enseignantIdPourUtilisateur($utilisateurId)
            ?? throw new ElementIntrouvableException('Profil enseignant introuvable.');

        [$du, $au] = Periode::resoudre($du, $au);

        return ['du' => $du, 'au' => $au, 'seances' => $this->seances->listerPourEnseignant($enseignantId, $du, $au)];
    }
}
