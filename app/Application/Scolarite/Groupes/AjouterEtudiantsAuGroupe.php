<?php

declare(strict_types=1);

namespace App\Application\Scolarite\Groupes;

use App\Domain\Referentiel\Exceptions\DonneesInvalidesException;
use App\Domain\Referentiel\Exceptions\RessourceIntrouvableException;
use App\Domain\Scolarite\Exceptions\CapaciteGroupeDepasseeException;
use App\Domain\Scolarite\Exceptions\EtudiantDejaDansUnGroupeException;
use App\Domain\Scolarite\Exceptions\FiliereIncompatibleException;
use App\Domain\Scolarite\Repositories\EtudiantRepository;
use App\Domain\Scolarite\Repositories\GroupeRepository;

final class AjouterEtudiantsAuGroupe
{
    public function __construct(
        private readonly GroupeRepository $repository,
        private readonly EtudiantRepository $etudiantRepository,
    ) {
    }

    /**
     * @param array{groupe_id:int, etudiant_ids:array<int, int>} $donnees
     */
    public function execute(array $donnees): void
    {
        $groupeId = (int) ($donnees['groupe_id'] ?? 0);
        $etudiantIds = $donnees['etudiant_ids'] ?? [];

        if ($groupeId <= 0 || $etudiantIds === []) {
            throw new DonneesInvalidesException('Le groupe et au moins un étudiant sont obligatoires.');
        }

        $groupe = $this->repository->trouverParId($groupeId);
        if ($groupe === null) {
            throw new RessourceIntrouvableException('Groupe', $groupeId);
        }

        $nouveaux = [];
        foreach ($etudiantIds as $etudiantId) {
            $id = (int) $etudiantId;
            if ($id <= 0) {
                throw new DonneesInvalidesException('Un identifiant étudiant est invalide.');
            }

            $etudiant = $this->etudiantRepository->trouverParId($id);
            if ($etudiant === null) {
                throw new RessourceIntrouvableException('Etudiant', $id);
            }

            if ($groupe->filiereId > 0 && $etudiant->filiereId !== null && $etudiant->filiereId > 0 && $etudiant->filiereId !== $groupe->filiereId) {
                throw new FiliereIncompatibleException();
            }

            $groupeExistant = $this->repository->groupeDeLEtudiantPourAnnee($id, $groupe->anneeId);
            if ($groupeExistant !== null && $groupeExistant->id !== $groupeId) {
                throw new EtudiantDejaDansUnGroupeException();
            }

            $nouveaux[] = $id;
        }

        $actuel = $groupe->effectifActuel;
        $ajout = count($nouveaux);
        if ($actuel + $ajout > $groupe->effectifMax) {
            throw new CapaciteGroupeDepasseeException();
        }

        $this->repository->ajouterEtudiantsAtomique($groupeId, $nouveaux);
    }
}
