<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\Scolarite\Entities\Etudiant;
use App\Domain\Scolarite\Repositories\EtudiantRepository;
use App\Infrastructure\Persistence\Eloquent\DatabaseExceptionTranslator;
use App\Infrastructure\Persistence\Eloquent\Models\Etudiant as EloquentEtudiant;
use Illuminate\Database\QueryException;

final class EloquentEtudiantRepository implements EtudiantRepository
{
    public function lister(?int $filiereId = null, ?string $q = null, int $page = 1, int $parPage = 20): array
    {
        $query = EloquentEtudiant::query()->with(['filiere', 'utilisateur']);

        if ($filiereId !== null) {
            $query->where('filiere_id', $filiereId);
        }

        if ($q !== null && trim($q) !== '') {
            $search = trim($q);
            $query->where(function ($subQuery) use ($search): void {
                $subQuery->where('matricule', 'like', '%' . $search . '%')
                    ->orWhere('nom', 'like', '%' . $search . '%')
                    ->orWhere('prenom', 'like', '%' . $search . '%');
            });
        }

        $models = $query->forPage($page, $parPage)->get();

        return $models->map(fn (EloquentEtudiant $model): Etudiant => $this->toDomain($model))->all();
    }

    public function trouverParId(int $id): ?Etudiant
    {
        $model = EloquentEtudiant::query()->with(['filiere', 'utilisateur'])->find($id);

        return $model === null ? null : $this->toDomain($model);
    }

    public function trouverParUtilisateurId(int $utilisateurId): ?Etudiant
    {
        $model = EloquentEtudiant::query()->with(['filiere', 'utilisateur'])->where('utilisateur_id', $utilisateurId)->first();

        return $model === null ? null : $this->toDomain($model);
    }

    public function modifierFiche(int $id, array $donnees): Etudiant
    {
        try {
            $model = EloquentEtudiant::query()->findOrFail($id);
            $model->fill($donnees);
            $model->save();

            return $this->toDomain($model->fresh(['filiere', 'utilisateur']));
        } catch (QueryException $exception) {
            DatabaseExceptionTranslator::translate($exception);
        }
    }

    private function toDomain(EloquentEtudiant $model): Etudiant
    {
        $filiere = $model->filiere;

        return new Etudiant(
            id: (int) $model->id,
            utilisateurId: (int) $model->utilisateur_id,
            matricule: (string) $model->matricule,
            nom: (string) $model->nom,
            prenom: (string) $model->prenom,
            filiereId: $model->filiere_id !== null ? (int) $model->filiere_id : null,
            filiereCode: $filiere?->code,
            filiereNom: $filiere?->nom,
        );
    }
}
