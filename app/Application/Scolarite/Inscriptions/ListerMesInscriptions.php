<?php

declare(strict_types=1);

namespace App\Application\Scolarite\Inscriptions;

use App\Domain\Referentiel\Exceptions\RessourceIntrouvableException;
use App\Domain\Scolarite\Repositories\EtudiantRepository;
use App\Domain\Scolarite\Repositories\InscriptionRepository;

final class ListerMesInscriptions
{
    public function __construct(
        private readonly EtudiantRepository $etudiantRepository,
        private readonly InscriptionRepository $inscriptionRepository,
    ) {
    }

    /**
     * @return array<int, array{id:int, etudiant_id:int, cours_id:int, annee_id:int, date_inscription:string}>
     */
    public function execute(int $utilisateurId): array
    {
        $etudiant = $this->etudiantRepository->trouverParUtilisateurId($utilisateurId);
        if ($etudiant === null) {
            throw new RessourceIntrouvableException('Etudiant', $utilisateurId);
        }

        $inscriptions = $this->inscriptionRepository->listerParEtudiant($etudiant->id);
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
