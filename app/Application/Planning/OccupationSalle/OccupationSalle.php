<?php

declare(strict_types=1);

namespace App\Application\Planning\OccupationSalle;

use App\Application\Planning\Periode;
use App\Domain\Planning\Entities\Seance;
use App\Domain\Planning\Exceptions\ElementIntrouvableException;
use App\Domain\Planning\Repositories\PlanningContexte;
use App\Domain\Planning\Repositories\SeanceRepository;

final class OccupationSalle
{
    public function __construct(
        private readonly SeanceRepository $seances,
        private readonly PlanningContexte $contexte,
    ) {
    }

    /** @return array{salle:array{id:int,nom:string,capacite:int},du:string,au:string,seances:Seance[]} */
    public function execute(int $salleId, ?string $du, ?string $au): array
    {
        $salle = $this->contexte->salle($salleId)
            ?? throw new ElementIntrouvableException('Salle introuvable.');

        [$du, $au] = Periode::resoudre($du, $au);

        return ['salle' => $salle, 'du' => $du, 'au' => $au, 'seances' => $this->seances->occupationSalle($salleId, $du, $au)];
    }
}
