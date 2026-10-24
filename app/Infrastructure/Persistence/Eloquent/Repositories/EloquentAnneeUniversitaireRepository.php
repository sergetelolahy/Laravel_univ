<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\Referentiel\Entities\AnneeUniversitaire as DomainAnneeUniversitaire;
use App\Domain\Referentiel\Repositories\AnneeUniversitaireRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\DatabaseExceptionTranslator;
use App\Infrastructure\Persistence\Eloquent\Models\AnneeUniversitaire as EloquentAnneeUniversitaire;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

final class EloquentAnneeUniversitaireRepository implements AnneeUniversitaireRepositoryInterface
{
    public function lister(array $filtres, int $page, int $parPage): array
    {
        $query = EloquentAnneeUniversitaire::query();

        if (isset($filtres['libelle'])) {
            $query->where('libelle', 'like', '%' . (string) $filtres['libelle'] . '%');
        }

        if (isset($filtres['courante'])) {
            $query->where('courante', (bool) $filtres['courante']);
        }

        $models = $query->forPage($page, $parPage)->get();

        return $models->map(fn (EloquentAnneeUniversitaire $model) => $this->toDomain($model))->all();
    }

    public function trouverParId(int $id): ?DomainAnneeUniversitaire
    {
        $model = EloquentAnneeUniversitaire::query()->find($id);

        return $model ? $this->toDomain($model) : null;
    }

    public function creer(array $donnees): DomainAnneeUniversitaire
    {
        try {
            $model = EloquentAnneeUniversitaire::query()->create([
                'libelle' => (string) $donnees['libelle'],
                'date_debut' => (string) $donnees['date_debut'],
                'date_fin' => (string) $donnees['date_fin'],
                'courante' => (bool) ($donnees['courante'] ?? false),
            ]);

            return $this->toDomain($model);
        } catch (QueryException $exception) {
            DatabaseExceptionTranslator::translate($exception);
        }
    }

    public function modifier(int $id, array $donnees): DomainAnneeUniversitaire
    {
        try {
            $model = EloquentAnneeUniversitaire::query()->findOrFail($id);
            $model->fill([
                'libelle' => (string) ($donnees['libelle'] ?? $model->libelle),
                'date_debut' => (string) ($donnees['date_debut'] ?? $model->date_debut->format('Y-m-d')),
                'date_fin' => (string) ($donnees['date_fin'] ?? $model->date_fin->format('Y-m-d')),
                'courante' => (bool) ($donnees['courante'] ?? $model->courante),
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
            $model = EloquentAnneeUniversitaire::query()->findOrFail($id);
            $model->delete();
        } catch (QueryException $exception) {
            DatabaseExceptionTranslator::translate($exception);
        }
    }

    public function existeLibelle(string $libelle, ?int $excludeId = null): bool
    {
        $query = EloquentAnneeUniversitaire::query()->where('libelle', $libelle);
        if ($excludeId !== null) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    public function definirCourante(int $id): void
    {
        try {
            DB::transaction(function () use ($id): void {
                EloquentAnneeUniversitaire::query()->update(['courante' => false]);
                EloquentAnneeUniversitaire::query()->where('id', $id)->update(['courante' => true]);
            });
        } catch (QueryException $exception) {
            DatabaseExceptionTranslator::translate($exception);
        }
    }

    private function toDomain(EloquentAnneeUniversitaire $model): DomainAnneeUniversitaire
    {
        return new DomainAnneeUniversitaire(
            id: (int) $model->id,
            libelle: (string) $model->libelle,
            dateDebut: new \DateTimeImmutable((string) $model->date_debut),
            dateFin: new \DateTimeImmutable((string) $model->date_fin),
            courante: (bool) $model->courante,
        );
    }
}
