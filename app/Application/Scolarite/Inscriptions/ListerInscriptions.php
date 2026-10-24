<?php

declare(strict_types=1);

namespace App\Application\Scolarite\Inscriptions;

use App\Domain\Scolarite\Repositories\InscriptionRepository;

final class ListerInscriptions
{
    public function __construct(private readonly InscriptionRepository $repository)
    {
    }

    /**
     * @return array<int, array{id:int, etudiant_id:int, cours_id:int, annee_id:int, date_inscription:string}>
     */
    public function execute(?int $etudiantId = null, ?int $coursId = null, ?int $anneeId = null, ?int $filiereId = null, int $page = 1, int $parPage = 20): array
    {
        $inscriptions = $this->repository->lister($etudiantId, $coursId, $anneeId, $filiereId, $page, $parPage);

        $result = [];
        foreach ($inscriptions as $inscription) {
            $result[] = [
                'id' => $inscription->id,
                'etudiant_id' => $inscription->etudiantId,
                'cours_id' => $inscription->coursId,
                'annee_id' => $inscription->anneeId,
                'date_inscription' => $inscription->dateInscription,
            ];
        }

        return $result;
    }
}
