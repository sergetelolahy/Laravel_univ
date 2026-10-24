<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\Referentiel\Entities\Salle as DomainSalle;
use App\Domain\Referentiel\Repositories\SalleRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\DatabaseExceptionTranslator;
use App\Infrastructure\Persistence\Eloquent\Models\Salle as EloquentSalle;
use Illuminate\Database\QueryException;

final class EloquentSalleRepository implements SalleRepositoryInterface
{
    public function lister(array $filtres, int $page, int $parPage): array
    {
        $query = EloquentSalle::query();

        if (isset($filtres['nom'])) {
            $query->where('nom', 'like', '%' . (string) $filtres['nom'] . '%');
        }

        $models = $query->forPage($page, $parPage)->get();

        return $models->map(fn (EloquentSalle $model) => $this->toDomain($model))->all();
    }

    public function trouverParId(int $id): ?DomainSalle
    {
        $model = EloquentSalle::query()->find($id);

        return $model ? $this->toDomain($model) : null;
    }

    public function creer(array $donnees): DomainSalle
    {
        try {
            $model = EloquentSalle::query()->create([
                'nom' => (string) $donnees['nom'],
                'capacite' => (int) $donnees['capacite'],
            ]);

            return $this->toDomain($model);
        } catch (QueryException $exception) {
            DatabaseExceptionTranslator::translate($exception);
        }
    }

    public function modifier(int $id, array $donnees): DomainSalle
    {
        try {
            $model = EloquentSalle::query()->findOrFail($id);
            $model->fill([
                'nom' => (string) ($donnees['nom'] ?? $model->nom),
                'capacite' => (int) ($donnees['capacite'] ?? $model->capacite),
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
            $model = EloquentSalle::query()->findOrFail($id);
            $model->delete();
        } catch (QueryException $exception) {
            DatabaseExceptionTranslator::translate($exception);
        }
    }

    public function existeNom(string $nom, ?int $excludeId = null): bool
    {
        $query = EloquentSalle::query()->where('nom', $nom);
        if ($excludeId !== null) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    private function toDomain(EloquentSalle $model): DomainSalle
    {
        return new DomainSalle(
            id: (int) $model->id,
            nom: (string) $model->nom,
            capacite: (int) $model->capacite,
        );
    }
}
