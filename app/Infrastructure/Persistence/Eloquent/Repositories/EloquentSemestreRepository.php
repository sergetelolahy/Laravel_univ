<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\Referentiel\Entities\Filiere as DomainFiliere;
use App\Domain\Referentiel\Entities\Semestre as DomainSemestre;
use App\Domain\Referentiel\Repositories\SemestreRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\DatabaseExceptionTranslator;
use App\Infrastructure\Persistence\Eloquent\Models\Semestre as EloquentSemestre;
use Illuminate\Database\QueryException;

final class EloquentSemestreRepository implements SemestreRepositoryInterface
{
    public function lister(array $filtres, int $page, int $parPage): array
    {
        $query = EloquentSemestre::query()->with('filiere');

        if (isset($filtres['filiere_id'])) {
            $query->where('filiere_id', (int) $filtres['filiere_id']);
        }

        if (isset($filtres['numero'])) {
            $query->where('numero', (int) $filtres['numero']);
        }

        $models = $query->forPage($page, $parPage)->get();

        return $models->map(fn (EloquentSemestre $model) => $this->toDomain($model))->all();
    }

    public function trouverParId(int $id): ?DomainSemestre
    {
        $model = EloquentSemestre::query()->with('filiere')->find($id);

        return $model ? $this->toDomain($model) : null;
    }

    public function creer(array $donnees): DomainSemestre
    {
        try {
            $model = EloquentSemestre::query()->create([
                'filiere_id' => (int) $donnees['filiere_id'],
                'numero' => (int) $donnees['numero'],
            ]);

            return $this->toDomain($model);
        } catch (QueryException $exception) {
            DatabaseExceptionTranslator::translate($exception);
        }
    }

    public function modifier(int $id, array $donnees): DomainSemestre
    {
        try {
            $model = EloquentSemestre::query()->findOrFail($id);
            $model->fill([
                'filiere_id' => (int) ($donnees['filiere_id'] ?? $model->filiere_id),
                'numero' => (int) ($donnees['numero'] ?? $model->numero),
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
            $model = EloquentSemestre::query()->findOrFail($id);
            $model->delete();
        } catch (QueryException $exception) {
            DatabaseExceptionTranslator::translate($exception);
        }
    }

    public function existeNumeroPourFiliere(int $filiereId, int $numero, ?int $excludeId = null): bool
    {
        $query = EloquentSemestre::query()->where('filiere_id', $filiereId)->where('numero', $numero);
        if ($excludeId !== null) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    private function toDomain(EloquentSemestre $model): DomainSemestre
    {
        $model->loadMissing('filiere');
        $filiere = $model->filiere;

        return new DomainSemestre(
            id: (int) $model->id,
            filiereId: (int) $model->filiere_id,
            numero: (int) $model->numero,
            filiere: $filiere !== null ? new DomainFiliere(
                id: (int) $filiere->id,
                code: (string) $filiere->code,
                nom: (string) $filiere->nom,
            ) : null,
        );
    }
}
