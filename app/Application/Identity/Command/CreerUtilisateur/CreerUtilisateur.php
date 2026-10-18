<?php

declare(strict_types=1);

namespace App\Application\Identity\Command\CreerUtilisateur;

use App\Domain\Identity\Entities\Utilisateur;
use App\Domain\Identity\Enums\Role;
use App\Domain\Identity\Exceptions\AccesRefuseException;
use App\Domain\Identity\Exceptions\EmailDejaUtiliseException;
use App\Domain\Identity\Repositories\UtilisateurRepository;
use App\Domain\Identity\Services\PasswordHasher;

final class CreerUtilisateur
{
    public function __construct(
        private readonly UtilisateurRepository $utilisateurRepository,
        private readonly PasswordHasher $passwordHasher,
    ) {
    }

    /**
     * @param array<string, mixed> $donnees
     * @return array{utilisateur: Utilisateur, profil: array<string, mixed>}
     */
    public function execute(int $utilisateurConnecteId, Role $roleConnecte, array $donnees): array
    {
        $roleCible = Role::from($donnees['role']);

        if ($roleConnecte !== Role::ADMIN && ($roleCible === Role::ADMIN || $roleCible === Role::SCOLARITE)) {
            throw new AccesRefuseException();
        }

        $email = strtolower(trim((string) $donnees['email']));
        if ($this->utilisateurRepository->emailExiste($email)) {
            throw new EmailDejaUtiliseException();
        }

        $profil = $this->normaliserProfil($roleCible, $donnees);
        $utilisateur = $this->utilisateurRepository->creerAvecProfil([
            'email' => $email,
            'motDePasseHash' => $this->passwordHasher->hacher((string) $donnees['motDePasse']),
            'role' => $roleCible->value,
            'actif' => (bool) ($donnees['actif'] ?? true),
            'derniereConnexion' => null,
        ], $profil);

        return [
            'utilisateur' => $utilisateur,
            'profil' => $profil,
        ];
    }

    /**
     * @param array<string, mixed> $donnees
     * @return array<string, mixed>
     */
    private function normaliserProfil(Role $role, array $donnees): array
    {
        if ($role === Role::ETUDIANT) {
            return [
                'type' => 'etudiant',
                'matricule' => (string) $donnees['matricule'],
                'nom' => (string) $donnees['nom'],
                'prenom' => (string) $donnees['prenom'],
                'filiere_id' => isset($donnees['filiere_id']) ? (int) $donnees['filiere_id'] : null,
            ];
        }

        if ($role === Role::ENSEIGNANT) {
            return [
                'type' => 'enseignant',
                'nom' => (string) $donnees['nom'],
                'prenom' => (string) $donnees['prenom'],
                'specialite' => isset($donnees['specialite']) ? (string) $donnees['specialite'] : null,
            ];
        }

        return [
            'type' => 'administratif',
            'nom' => isset($donnees['nom']) ? (string) $donnees['nom'] : null,
            'prenom' => isset($donnees['prenom']) ? (string) $donnees['prenom'] : null,
        ];
    }
}
