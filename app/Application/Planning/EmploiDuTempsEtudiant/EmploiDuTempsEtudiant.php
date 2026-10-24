<?php

declare(strict_types=1);

namespace App\Application\Planning\EmploiDuTempsEtudiant;

use App\Application\Planning\Periode;
use App\Domain\Planning\Entities\Seance;
use App\Domain\Planning\Exceptions\ElementIntrouvableException;
use App\Domain\Planning\Repositories\PlanningContexte;
use App\Domain\Planning\Repositories\SeanceRepository;

final class EmploiDuTempsEtudiant
{
    public function __construct(
        private readonly SeanceRepository $seances,
        private readonly PlanningContexte $contexte,
    ) {
    }

    /** @return array{du:string,au:string,seances:Seance[]} */
    public function execute(int $utilisateurId, ?string $du, ?string $au): array
    {
        $etudiantId = $this->contexte->etudiantIdPourUtilisateur($utilisateurId)
            ?? throw new ElementIntrouvableException('Profil étudiant introuvable.');

        [$du, $au] = Periode::resoudre($du, $au);

        return ['du' => $du, 'au' => $au, 'seances' => $this->seances->listerPourEtudiant($etudiantId, $du, $au)];
    }
}
