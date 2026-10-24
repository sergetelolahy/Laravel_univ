<?php

declare(strict_types=1);

namespace App\Application\Planning;

final class DonneesSeance
{
    public function __construct(
        public readonly int $coursId,
        public readonly int $groupeId,
        public readonly int $enseignantId,
        public readonly int $salleId,
        public readonly \DateTimeImmutable $debut,
        public readonly \DateTimeImmutable $fin,
    ) {
    }

    /** @return array{cours_id:int,groupe_id:int,enseignant_id:int,salle_id:int,debut:string,fin:string} */
    public function versTableau(): array
    {
        return [
            'cours_id' => $this->coursId,
            'groupe_id' => $this->groupeId,
            'enseignant_id' => $this->enseignantId,
            'salle_id' => $this->salleId,
            'debut' => $this->debut->format('Y-m-d H:i:s'),
            'fin' => $this->fin->format('Y-m-d H:i:s'),
        ];
    }
}
