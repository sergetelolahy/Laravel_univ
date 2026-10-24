<?php

declare(strict_types=1);

namespace App\Application\Scolarite\Inscriptions;

use App\Domain\Referentiel\Exceptions\ConflitException;
use App\Domain\Referentiel\Exceptions\DonneesInvalidesException;
use App\Domain\Referentiel\Exceptions\RessourceIntrouvableException;
use App\Domain\Scolarite\Exceptions\AccesRefuseException;
use App\Domain\Scolarite\Exceptions\AnneeNonCouranteException;
use App\Domain\Scolarite\Exceptions\FiliereIncompatibleException;
use App\Domain\Scolarite\Exceptions\InscriptionDejaExistanteException;
use App\Domain\Scolarite\Repositories\EnseignementRepository;
use App\Domain\Scolarite\Repositories\EtudiantRepository;
use App\Domain\Scolarite\Repositories\InscriptionRepository;

final class InscrireAUnCours
{
    public function __construct(
        private readonly EtudiantRepository $etudiantRepository,
        private readonly InscriptionRepository $inscriptionRepository,
        private readonly EnseignementRepository $enseignementRepository,
    ) {
    }

    /**
     * @param array{
     *     etudiant_id:int,
     *     cours_id:int,
     *     annee_id:int,
     *     utilisateur_id?:int,
     *     role?:string
     * } $donnees
     *
     * @return array<string, mixed>
     */
    public function execute(array $donnees): array
    {
        $etudiantId = (int) ($donnees['etudiant_id'] ?? 0);
        $coursId = (int) ($donnees['cours_id'] ?? 0);
        $anneeId = (int) ($donnees['annee_id'] ?? 0);
        $utilisateurId = (int) ($donnees['utilisateur_id'] ?? 0);
        $role = (string) ($donnees['role'] ?? '');

        // 1. Vérifier les données obligatoires.
        if ($etudiantId <= 0 || $coursId <= 0 || $anneeId <= 0) {
            throw new DonneesInvalidesException(
                'L’étudiant, le cours et l’année sont obligatoires.'
            );
        }

        // 2. Vérifier l’étudiant.
        if ($role === 'ETUDIANT') {
            $etudiant = $this->etudiantRepository
                ->trouverParUtilisateurId($utilisateurId);

            if ($etudiant === null) {
                throw new RessourceIntrouvableException(
                    'Etudiant',
                    $utilisateurId
                );
            }

            $etudiantId = $etudiant->id;
        } else {
            $etudiant = $this->etudiantRepository
                ->trouverParId($etudiantId);

            if ($etudiant === null) {
                throw new RessourceIntrouvableException(
                    'Etudiant',
                    $etudiantId
                );
            }
        }

        // 3. Vérifier l’année universitaire et les autorisations.
        $anneeCourante = $this->enseignementRepository->anneeCourante();

        $estMonCompte = $utilisateurId > 0
            && $etudiant->utilisateurId === $utilisateurId;

        $estScolarite = in_array(
            $role,
            ['SCOLARITE', 'ADMIN'],
            true
        );

        if ($role === 'ETUDIANT' || $estMonCompte) {
            if (
                $anneeCourante === null
                || $anneeId !== $anneeCourante
            ) {
                throw new AnneeNonCouranteException();
            }
        } elseif (!$estScolarite) {
            throw new AccesRefuseException(
                'Vous ne pouvez pas inscrire cet étudiant pour ce cours.'
            );
        }

        // 4. Empêcher une inscription en double.
        if (
            $this->inscriptionRepository->existe(
                $etudiantId,
                $coursId,
                $anneeId
            )
        ) {
            throw new InscriptionDejaExistanteException();
        }

        // 5. Vérifier la compatibilité de la filière.
        $filiereEtudiant = $etudiant->filiereId ?? 0;

        $filiereCours = $this->enseignementRepository
            ->filierePourCours($coursId);

        if (
            $filiereEtudiant > 0
            && $filiereCours !== $filiereEtudiant
        ) {
            throw new FiliereIncompatibleException();
        }

        // 6. Créer l’inscription.
        $inscription = $this->inscriptionRepository->creer([
            'etudiant_id' => $etudiantId,
            'cours_id' => $coursId,
            'annee_id' => $anneeId,
        ]);

        // 7. Retourner les données disponibles.
        return [
            'id' => $inscription->id,
            'etudiant_id' => $inscription->etudiantId,
            'cours_id' => $inscription->coursId,
            'annee_id' => $inscription->anneeId,
            'date_inscription' => $inscription->dateInscription,

            'etudiant' => [
                'id' => $etudiant->id,
                'matricule' => $etudiant->matricule ?? null,
                'nom' => $etudiant->nom ?? null,
                'prenom' => $etudiant->prenom ?? null,
            ],

            // Les détails du cours et de l’année nécessitent
            // de connaître les méthodes disponibles dans les repositories.
            'cours' => null,
            'annee_universitaire' => [
                'id' => $anneeId,
            ],
        ];
    }
}