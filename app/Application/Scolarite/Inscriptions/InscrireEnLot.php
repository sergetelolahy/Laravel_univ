<?php

declare(strict_types=1);

namespace App\Application\Scolarite\Inscriptions;

use App\Domain\Referentiel\Exceptions\ConflitException;
use App\Domain\Referentiel\Exceptions\DonneesInvalidesException;
use App\Domain\Scolarite\Exceptions\AccesRefuseException;
use App\Domain\Scolarite\Exceptions\AnneeNonCouranteException;
use App\Domain\Scolarite\Exceptions\FiliereIncompatibleException;
use App\Domain\Scolarite\Exceptions\InscriptionDejaExistanteException;
use App\Domain\Scolarite\Repositories\EnseignementRepository;
use App\Domain\Scolarite\Repositories\EtudiantRepository;
use App\Domain\Scolarite\Repositories\InscriptionRepository;

final class InscrireEnLot
{
    public function __construct(
        private readonly EtudiantRepository $etudiantRepository,
        private readonly InscriptionRepository $inscriptionRepository,
        private readonly EnseignementRepository $enseignementRepository,
    ) {
    }

    /**
     * @param array{inscriptions: array<int, array{etudiant_id:int, cours_id:int, annee_id:int}>, utilisateur_id?:int, role?:string} $donnees
     */
    public function execute(array $donnees): array
    {
        $utilisateurId = (int) ($donnees['utilisateur_id'] ?? 0);
        $role = (string) ($donnees['role'] ?? '');
        $inscriptions = $donnees['inscriptions'] ?? [];

        if ($inscriptions === []) {
            throw new DonneesInvalidesException('La liste des inscriptions est obligatoire.');
        }

        $liste = [];
        $vus = [];
        $anneeCourante = $this->enseignementRepository->anneeCourante();
        $estScolarite = ($role === 'SCOLARITE' || $role === 'ADMIN');

        foreach ($inscriptions as $inscription) {
            $etudiantId = (int) ($inscription['etudiant_id'] ?? 0);
            $coursId = (int) ($inscription['cours_id'] ?? 0);
            $anneeId = (int) ($inscription['annee_id'] ?? 0);

            if ($etudiantId <= 0 || $coursId <= 0 || $anneeId <= 0) {
                throw new DonneesInvalidesException('Chaque inscription doit avoir un étudiant, un cours et une année valides.');
            }

            $cle = "{$etudiantId}_{$coursId}_{$anneeId}";
            if (isset($vus[$cle]) || $this->inscriptionRepository->existe($etudiantId, $coursId, $anneeId)) {
                throw new InscriptionDejaExistanteException();
            }
            $vus[$cle] = true;

            $etudiant = $this->etudiantRepository->trouverParId($etudiantId);
            if ($etudiant === null) {
                throw new ConflitException('Étudiant introuvable lors de l\'inscription en lot.');
            }

            $estMonCompte = $utilisateurId > 0 && $etudiant->utilisateurId === $utilisateurId;
            if ($estMonCompte) {
                if ($anneeCourante === null || $anneeId !== $anneeCourante) {
                    throw new AnneeNonCouranteException();
                }
            } elseif (!$estScolarite) {
                throw new AccesRefuseException('Vous ne pouvez pas inscrire ce lot d\'étudiants.');
            }

            $filiereEtudiant = $etudiant->filiereId ?? 0;
            $filiereCours = $this->enseignementRepository->filierePourCours($coursId);
            if ($filiereEtudiant > 0 && $filiereCours !== $filiereEtudiant) {
                throw new FiliereIncompatibleException();
            }

            $liste[] = [
                'etudiant_id' => $etudiantId,
                'cours_id' => $coursId,
                'annee_id' => $anneeId,
            ];
        }

        $this->inscriptionRepository->creerEnLotAtomique($liste);

        return [
            'nombre' => count($liste),
            'inscriptions' => $liste,
        ];
    }
}
