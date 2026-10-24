<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\Referentiel\Entities\Filiere as DomainFiliere;
use App\Domain\Referentiel\Repositories\FiliereRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\DatabaseExceptionTranslator;
use App\Infrastructure\Persistence\Eloquent\Models\Filiere as EloquentFiliere;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

final class EloquentFiliereRepository implements FiliereRepositoryInterface
{
    public function lister(array $filtres, int $page, int $parPage): array
    {
        $query = EloquentFiliere::query();

        if (isset($filtres['code'])) {
            $query->where('code', 'like', '%' . (string) $filtres['code'] . '%');
        }

        if (isset($filtres['nom'])) {
            $query->where('nom', 'like', '%' . (string) $filtres['nom'] . '%');
        }

        $models = $query->forPage($page, $parPage)->get();

        return $models->map(fn (EloquentFiliere $model) => $this->toDomain($model))->all();
    }

    public function trouverParId(int $id): ?DomainFiliere
    {
        $model = EloquentFiliere::query()->find($id);

        return $model ? $this->toDomain($model) : null;
    }

    public function creer(array $donnees): DomainFiliere
    {
        try {
            $model = EloquentFiliere::query()->create([
                'code' => (string) $donnees['code'],
                'nom' => (string) $donnees['nom'],
            ]);

            return $this->toDomain($model);
        } catch (QueryException $exception) {
            DatabaseExceptionTranslator::translate($exception);
        }
    }

    public function modifier(int $id, array $donnees): DomainFiliere
    {
        try {
            $model = EloquentFiliere::query()->findOrFail($id);
            $model->fill([
                'code' => (string) ($donnees['code'] ?? $model->code),
                'nom' => (string) ($donnees['nom'] ?? $model->nom),
            ]);
            $model->save();

            return $this->toDomain($model->fresh());
        } catch (QueryException $exception) {
            DatabaseExceptionTranslator::translate($exception);
        }
    }

    public function supprimer(int $id): void
    {
        try {
            $model = EloquentFiliere::query()->findOrFail($id);
            $model->delete();
        } catch (QueryException $exception) {
            DatabaseExceptionTranslator::translate($exception);
        }
    }

    public function existeCode(string $code, ?int $excludeId = null): bool
    {
        $query = EloquentFiliere::query()->where('code', $code);
        if ($excludeId !== null) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    public function existeNom(string $nom, ?int $excludeId = null): bool
    {
        $query = EloquentFiliere::query()->where('nom', $nom);
        if ($excludeId !== null) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    private function toDomain(EloquentFiliere $model): DomainFiliere
    {
        return new DomainFiliere(
            id: (int) $model->id,
            code: (string) $model->code,
            nom: (string) $model->nom,
        );
    }
}
