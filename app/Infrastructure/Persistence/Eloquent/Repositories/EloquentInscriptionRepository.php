<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\Scolarite\Entities\Inscription;
use App\Domain\Scolarite\Repositories\InscriptionRepository;
use App\Infrastructure\Persistence\Eloquent\DatabaseExceptionTranslator;
use App\Infrastructure\Persistence\Eloquent\Models\InscriptionCours as EloquentInscriptionCours;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

final class EloquentInscriptionRepository implements InscriptionRepository
{
    public function creer(array $donnees): Inscription
    {
        try {
            $model = EloquentInscriptionCours::query()->create([
                'etudiant_id' => (int) $donnees['etudiant_id'],
                'cours_id' => (int) $donnees['cours_id'],
                'annee_id' => (int) $donnees['annee_id'],
                'date_inscription' => now()->format('Y-m-d H:i:s'),
            ]);

            return $this->toDomain($model->fresh(['etudiant', 'cours', 'anneeUniversitaire']));
        } catch (QueryException $exception) {
            DatabaseExceptionTranslator::translate($exception);
        }
    }

    public function creerEnLotAtomique(array $inscriptions): void
    {
        if ($inscriptions === []) {
            return;
        }

        try {
            DB::transaction(function () use ($inscriptions): void {
                $rows = [];
                foreach ($inscriptions as $inscription) {
                    $rows[] = [
                        'etudiant_id' => (int) $inscription['etudiant_id'],
                        'cours_id' => (int) $inscription['cours_id'],
                        'annee_id' => (int) $inscription['annee_id'],
                        'date_inscription' => now()->format('Y-m-d H:i:s'),
                    ];
                }

                EloquentInscriptionCours::query()->insert($rows);
            });
        } catch (QueryException $exception) {
            DatabaseExceptionTranslator::translate($exception);
        }
    }

    public function lister(?int $etudiantId = null, ?int $coursId = null, ?int $anneeId = null, ?int $filiereId = null, int $page = 1, int $parPage = 20): array
    {
        $query = EloquentInscriptionCours::query()->with(['etudiant', 'cours.semestre.filiere', 'anneeUniversitaire']);

        if ($etudiantId !== null) {
            $query->where('etudiant_id', $etudiantId);
        }

        if ($coursId !== null) {
            $query->where('cours_id', $coursId);
        }

        if ($anneeId !== null) {
            $query->where('annee_id', $anneeId);
        }

        if ($filiereId !== null) {
            $query->whereHas('cours.semestre', function ($subQuery) use ($filiereId): void {
                $subQuery->where('filiere_id', $filiereId);
            });
        }

        $models = $query->forPage($page, $parPage)->get();

        return $models->map(fn (EloquentInscriptionCours $model): Inscription => $this->toDomain($model))->all();
    }

    public function listerParEtudiant(int $etudiantId): array
    {
        $models = EloquentInscriptionCours::query()->with(['etudiant', 'cours.semestre.filiere', 'anneeUniversitaire'])
            ->where('etudiant_id', $etudiantId)
            ->orderByDesc('date_inscription')
            ->get();

        return $models->map(fn (EloquentInscriptionCours $model): Inscription => $this->toDomain($model))->all();
    }

    public function listerInscritsDuCours(int $coursId, ?int $anneeId = null): array
    {
        $query = EloquentInscriptionCours::query()->with(['etudiant', 'cours.semestre.filiere', 'anneeUniversitaire'])
            ->where('cours_id', $coursId);

        if ($anneeId !== null) {
            $query->where('annee_id', $anneeId);
        }

        $models = $query->orderByDesc('date_inscription')->get();

        return $models->map(fn (EloquentInscriptionCours $model): Inscription => $this->toDomain($model))->all();
    }

    public function supprimer(int $id): void
    {
        try {
            $model = EloquentInscriptionCours::query()->findOrFail($id);
            $model->delete();
        } catch (QueryException $exception) {
            DatabaseExceptionTranslator::translate($exception);
        }
    }

    public function trouverParId(int $id): ?Inscription
    {
        $model = EloquentInscriptionCours::query()->with(['etudiant', 'cours.semestre.filiere', 'anneeUniversitaire'])->find($id);

        return $model === null ? null : $this->toDomain($model);
    }

    public function existe(int $etudiantId, int $coursId, int $anneeId): bool
    {
        return EloquentInscriptionCours::query()
            ->where('etudiant_id', $etudiantId)
            ->where('cours_id', $coursId)
            ->where('annee_id', $anneeId)
            ->exists();
    }

    private function toDomain(EloquentInscriptionCours $model): Inscription
    {
        $etudiant = $model->etudiant;
        $cours = $model->cours;
        $annee = $model->anneeUniversitaire;

        return new Inscription(
            id: (int) $model->id,
            etudiantId: (int) $model->etudiant_id,
            coursId: (int) $model->cours_id,
            anneeId: (int) $model->annee_id,
            dateInscription: $model->date_inscription !== null ? $model->date_inscription->format('Y-m-d H:i:s') : '',
            etudiantMatricule: $etudiant?->matricule,
            etudiantNom: $etudiant?->nom,
            etudiantPrenom: $etudiant?->prenom,
            coursCode: $cours?->code,
            coursIntitule: $cours?->intitule,
            coursSemestreId: $cours?->semestre_id !== null ? (int) $cours->semestre_id : null,
            anneeLibelle: $annee?->libelle,
        );
    }
}
