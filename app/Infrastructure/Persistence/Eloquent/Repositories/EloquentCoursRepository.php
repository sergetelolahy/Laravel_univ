<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\Referentiel\Entities\Cours as DomainCours;
use App\Domain\Referentiel\Entities\Filiere as DomainFiliere;
use App\Domain\Referentiel\Entities\Semestre as DomainSemestre;
use App\Domain\Referentiel\Repositories\CoursRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\DatabaseExceptionTranslator;
use App\Infrastructure\Persistence\Eloquent\Models\Cours as EloquentCours;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

final class EloquentCoursRepository implements CoursRepositoryInterface
{
    public function lister(array $filtres, int $page, int $parPage): array
    {
        $query = EloquentCours::query()->with(['semestre.filiere']);

        if (isset($filtres['code'])) {
            $query->where('code', 'like', '%' . (string) $filtres['code'] . '%');
        }

        if (isset($filtres['semestre_id'])) {
            $query->where('semestre_id', (int) $filtres['semestre_id']);
        }

        if (isset($filtres['filiere_id'])) {
            $query->whereHas('semestre', static fn ($semestreQuery) => $semestreQuery->where('filiere_id', (int) $filtres['filiere_id']));
        }

        if (isset($filtres['q'])) {
            $q = trim((string) $filtres['q']);
            if ($q !== '') {
                $query->where(static function ($subQuery) use ($q): void {
                    $subQuery->where('code', 'like', '%' . $q . '%')
                        ->orWhere('intitule', 'like', '%' . $q . '%');
                });
            }
        }

        $models = $query->forPage($page, $parPage)->get();

        return $models->map(fn (EloquentCours $model) => $this->toDomain($model))->all();
    }

    public function trouverParId(int $id): ?DomainCours
    {
        $model = EloquentCours::query()->with(['semestre.filiere'])->find($id);

        return $model ? $this->toDomain($model) : null;
    }

    public function creer(array $donnees): DomainCours
    {
        try {
            $model = EloquentCours::query()->create([
                'code' => (string) $donnees['code'],
                'intitule' => (string) $donnees['intitule'],
                'semestre_id' => (int) $donnees['semestre_id'],
                'coefficient' => (float) $donnees['coefficient'],
                'credits' => (int) $donnees['credits'],
            ]);

            return $this->toDomain($model);
        } catch (QueryException $exception) {
            DatabaseExceptionTranslator::translate($exception);
        }
    }

    public function modifier(int $id, array $donnees): DomainCours
    {
        try {
            $model = EloquentCours::query()->findOrFail($id);
            $model->fill([
                'code' => (string) ($donnees['code'] ?? $model->code),
                'intitule' => (string) ($donnees['intitule'] ?? $model->intitule),
                'semestre_id' => (int) ($donnees['semestre_id'] ?? $model->semestre_id),
                'coefficient' => (float) ($donnees['coefficient'] ?? $model->coefficient),
                'credits' => (int) ($donnees['credits'] ?? $model->credits),
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
            $model = EloquentCours::query()->findOrFail($id);
            $model->delete();
        } catch (QueryException $exception) {
            DatabaseExceptionTranslator::translate($exception);
        }
    }

    public function existeCode(string $code, ?int $excludeId = null): bool
    {
        $query = EloquentCours::query()->where('code', $code);
        if ($excludeId !== null) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    public function affecterEnseignant(int $coursId, int $enseignantId, int $anneeId): void
    {
        try {
            DB::table('enseignant_cours')->insert([
                'enseignant_id' => $enseignantId,
                'cours_id' => $coursId,
                'annee_id' => $anneeId,
            ]);
        } catch (QueryException $exception) {
            DatabaseExceptionTranslator::translate($exception);
        }
    }

    public function retirerEnseignant(int $coursId, int $enseignantId, int $anneeId): void
    {
        try {
            DB::table('enseignant_cours')
                ->where('enseignant_id', $enseignantId)
                ->where('cours_id', $coursId)
                ->where('annee_id', $anneeId)
                ->delete();
        } catch (QueryException $exception) {
            DatabaseExceptionTranslator::translate($exception);
        }
    }

    public function listerEnseignants(int $coursId, ?int $anneeId = null): array
    {
        $query = DB::table('enseignant_cours as ec')
            ->join('enseignant as e', 'e.id', '=', 'ec.enseignant_id')
            ->join('utilisateur as u', 'u.id', '=', 'e.utilisateur_id')
            ->where('ec.cours_id', $coursId)
            ->select([
                'e.id as id',
                'e.nom as nom',
                'e.prenom as prenom',
                'e.specialite as specialite',
                'u.email as email',
            ]);

        if ($anneeId !== null) {
            $query->where('ec.annee_id', $anneeId);
        }

        return $query->distinct()->get()->map(static fn ($row): array => [
            'id' => (int) $row->id,
            'nom' => (string) $row->nom,
            'prenom' => (string) $row->prenom,
            'specialite' => $row->specialite !== null ? (string) $row->specialite : null,
            'email' => (string) $row->email,
        ])->all();
    }

    private function toDomain(EloquentCours $model): DomainCours
    {
        $model->loadMissing(['semestre.filiere']);
        $semestre = $model->semestre;
        $filiere = $semestre?->filiere;

        return new DomainCours(
            id: (int) $model->id,
            code: (string) $model->code,
            intitule: (string) $model->intitule,
            semestreId: (int) $model->semestre_id,
            coefficient: (float) $model->coefficient,
            credits: (int) $model->credits,
            semestre: $semestre !== null ? new DomainSemestre(
                id: (int) $semestre->id,
                filiereId: (int) $semestre->filiere_id,
                numero: (int) $semestre->numero,
                filiere: $filiere !== null ? new DomainFiliere(
                    id: (int) $filiere->id,
                    code: (string) $filiere->code,
                    nom: (string) $filiere->nom,
                ) : null,
            ) : null,
        );
    }
}
