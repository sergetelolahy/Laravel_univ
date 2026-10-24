<?php

declare(strict_types=1);

namespace App\Application\Scolarite\Inscriptions;

use App\Domain\Referentiel\Exceptions\RessourceIntrouvableException;
use App\Domain\Scolarite\Exceptions\AccesRefuseException;
use App\Domain\Scolarite\Repositories\EnseignementRepository;
use App\Domain\Scolarite\Repositories\InscriptionRepository;

final class ListerInscritsDuCours
{
    public function __construct(
        private readonly InscriptionRepository $inscriptionRepository,
        private readonly EnseignementRepository $enseignementRepository,
    ) {
    }

    /**
     * @return array<int, array{id:int, etudiant_id:int, cours_id:int, annee_id:int, date_inscription:string}>
     */
    public function execute(int $coursId, ?int $anneeId = null, ?int $utilisateurId = null, ?string $role = null): array
    {
        if ($coursId <= 0) {
            throw new RessourceIntrouvableException('Cours', $coursId);
        }

        if (($role === 'ADMIN' || $role === 'SCOLARITE')) {
            $inscriptions = $this->inscriptionRepository->listerInscritsDuCours($coursId, $anneeId);
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

        if ($role === 'ENSEIGNANT') {
            $annee = $anneeId ?? $this->enseignementRepository->anneeCourante();
            if ($annee === null || !$this->enseignementRepository->enseignantAffecteAuCours((int) $utilisateurId, $coursId, $annee)) {
                throw new AccesRefuseException('Vous n\'êtes pas affecté à ce cours pour cette année.');
            }

            $inscriptions = $this->inscriptionRepository->listerInscritsDuCours($coursId, $annee);
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

        throw new AccesRefuseException('Accès refusé à la liste des inscrits du cours.');
    }
}
