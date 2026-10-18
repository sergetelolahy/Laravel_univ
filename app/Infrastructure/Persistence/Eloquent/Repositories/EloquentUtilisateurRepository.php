<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\Identity\Entities\Utilisateur as DomainUtilisateur;
use App\Domain\Identity\Enums\Role;
use App\Domain\Identity\Exceptions\EmailDejaUtiliseException;
use App\Domain\Identity\Repositories\UtilisateurRepository;
use App\Infrastructure\Persistence\Eloquent\Models\Utilisateur as EloquentUtilisateur;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

final class EloquentUtilisateurRepository implements UtilisateurRepository
{
    public function trouverParEmail(string $email): ?DomainUtilisateur
    {
        $model = EloquentUtilisateur::query()->where('email', $email)->first();

        return $model ? $this->toDomain($model) : null;
    }

    public function trouverParId(int $id): ?DomainUtilisateur
    {
        $model = EloquentUtilisateur::query()->find($id);

        return $model ? $this->toDomain($model) : null;
    }

    public function emailExiste(string $email): bool
    {
        return EloquentUtilisateur::query()->where('email', $email)->exists();
    }

    public function creerAvecProfil(array $compte, array $profil): DomainUtilisateur
    {
        try {
            return DB::transaction(function () use ($compte, $profil): DomainUtilisateur {
                $roleValeur = $compte['role'] instanceof Role
                    ? $compte['role']->value
                    : $compte['role'];

                $model = EloquentUtilisateur::query()->create([
                    'email' => $compte['email'],
                    'mot_de_passe_hash' => $compte['motDePasseHash'],
                    'role' => $roleValeur,
                    'actif' => $compte['actif'] ?? true,
                    'derniere_connexion' => $compte['derniereConnexion'] ?? null,
                ]);

                if (($profil['type'] ?? null) === 'etudiant') {
                    $model->etudiant()->create([
                        'matricule' => $profil['matricule'],
                        'nom' => $profil['nom'],
                        'prenom' => $profil['prenom'],
                        'date_naissance' => $profil['date_naissance'] ?? null,
                        'filiere_id' => $profil['filiere_id'] ?? null,
                    ]);
                }

                if (($profil['type'] ?? null) === 'enseignant') {
                    $model->enseignant()->create([
                        'nom' => $profil['nom'],
                        'prenom' => $profil['prenom'],
                        'specialite' => $profil['specialite'] ?? null,
                    ]);
                }

                return $this->toDomain($model->fresh());
            });
        } catch (QueryException $exception) {
            if (($exception->errorInfo[1] ?? null) === 1062) {
                throw new EmailDejaUtiliseException();
            }

            throw $exception;
        }
    }

    public function lister(?Role $role, int $page, int $parPage): array
    {
        $query = EloquentUtilisateur::query();

        if ($role !== null) {
            $query->where('role', $role->value);
        }

        $result = $query->paginate($parPage, ['*'], 'page', $page);

        /** @var array<int, DomainUtilisateur> $utilisateurs */
        $utilisateurs = [];
        foreach ($result as $model) {
            $utilisateurs[] = $this->toDomain($model);
        }

        return $utilisateurs;
    }

    public function desactiver(int $id): void
    {
        EloquentUtilisateur::query()->where('id', $id)->update(['actif' => false]);
    }

    public function enregistrerConnexion(int $id): void
    {
        EloquentUtilisateur::query()->where('id', $id)->update([
            'derniere_connexion' => now(),
        ]);
    }

    private function toDomain(EloquentUtilisateur $model): DomainUtilisateur
    {
        $role = $model->role instanceof Role ? $model->role : Role::from((string) $model->role);
        $derniereConnexion = $model->derniere_connexion !== null
            ? new \DateTimeImmutable((string) $model->derniere_connexion)
            : null;

        return new DomainUtilisateur(
            id: (int) $model->id,
            email: (string) $model->email,
            motDePasseHash: (string) $model->mot_de_passe_hash,
            role: $role,
            actif: (bool) $model->actif,
            derniereConnexion: $derniereConnexion,
            profil: $this->buildProfil($model),
        );
    }

    private function buildProfil(EloquentUtilisateur $model): ?array
    {
        if ($model->etudiant !== null) {
            $etudiant = $model->etudiant;

            return [
                'type' => 'etudiant',
                'matricule' => $etudiant?->matricule,
                'nom' => $etudiant?->nom,
                'prenom' => $etudiant?->prenom,
                'date_naissance' => $etudiant?->date_naissance instanceof \DateTimeInterface
                    ? $etudiant->date_naissance->format('Y-m-d')
                    : $etudiant?->date_naissance,
                'filiere_id' => $etudiant?->filiere_id,
            ];
        }

        if ($model->enseignant !== null) {
            $enseignant = $model->enseignant;

            return [
                'type' => 'enseignant',
                'nom' => $enseignant?->nom,
                'prenom' => $enseignant?->prenom,
                'specialite' => $enseignant?->specialite,
            ];
        }

        return null;
    }
}
