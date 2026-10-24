<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\Scolarite\Entities\Etudiant as DomainEtudiant;
use App\Domain\Scolarite\Entities\Groupe;
use App\Domain\Scolarite\Exceptions\CapaciteGroupeDepasseeException;
use App\Domain\Scolarite\Exceptions\EtudiantDejaDansUnGroupeException;
use App\Domain\Scolarite\Repositories\GroupeRepository;
use App\Infrastructure\Persistence\Eloquent\DatabaseExceptionTranslator;
use App\Infrastructure\Persistence\Eloquent\Models\Etudiant as EloquentEtudiant;
use App\Infrastructure\Persistence\Eloquent\Models\Groupe as EloquentGroupe;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

final class EloquentGroupeRepository implements GroupeRepository
{
    public function lister(?int $filiereId = null, ?int $anneeId = null, ?string $q = null, int $page = 1, int $parPage = 20): array
    {
        $query = EloquentGroupe::query()->with(['filiere', 'anneeUniversitaire'])->withCount('etudiants');

        if ($filiereId !== null) {
            $query->where('filiere_id', $filiereId);
        }

        if ($anneeId !== null) {
            $query->where('annee_id', $anneeId);
        }

        if ($q !== null && trim($q) !== '') {
            $search = trim($q);
            $query->where('nom', 'like', '%' . $search . '%');
        }

        $models = $query->forPage($page, $parPage)->get();

        return $models->map(fn (EloquentGroupe $model): Groupe => $this->toDomain($model))->all();
    }

    public function trouverParId(int $id): ?Groupe
    {
        $model = EloquentGroupe::query()->with(['filiere', 'anneeUniversitaire'])->withCount('etudiants')->find($id);

        return $model === null ? null : $this->toDomain($model);
    }

    public function creer(array $donnees): Groupe
    {
        try {
            $model = EloquentGroupe::query()->create([
                'nom' => (string) $donnees['nom'],
                'filiere_id' => (int) $donnees['filiere_id'],
                'annee_id' => (int) $donnees['annee_id'],
                'effectif_max' => (int) $donnees['effectif_max'],
            ]);

            $model->refresh();

            return $this->toDomain($model);
        } catch (QueryException $exception) {
            DatabaseExceptionTranslator::translate($exception);
        }
    }

    public function modifier(int $id, array $donnees): Groupe
    {
        try {
            $model = EloquentGroupe::query()->findOrFail($id);
            $model->fill($donnees);
            $model->save();

            return $this->toDomain($model->fresh(['filiere', 'anneeUniversitaire']));
        } catch (QueryException $exception) {
            DatabaseExceptionTranslator::translate($exception);
        }
    }

    public function supprimer(int $id): void
    {
        try {
            $model = EloquentGroupe::query()->findOrFail($id);
            $model->delete();
        } catch (QueryException $exception) {
            DatabaseExceptionTranslator::translate($exception);
        }
    }

    public function listerEtudiants(int $groupeId): array
    {
        $groupe = EloquentGroupe::query()->with(['etudiants.filiere', 'etudiants.utilisateur'])->find($groupeId);
        if ($groupe === null) {
            return [];
        }

        return $groupe->etudiants->map(fn (EloquentEtudiant $etudiant): DomainEtudiant => new DomainEtudiant(
            id: (int) $etudiant->id,
            utilisateurId: (int) $etudiant->utilisateur_id,
            matricule: (string) $etudiant->matricule,
            nom: (string) $etudiant->nom,
            prenom: (string) $etudiant->prenom,
            filiereId: $etudiant->filiere_id !== null ? (int) $etudiant->filiere_id : null,
            filiereCode: $etudiant->filiere?->code,
            filiereNom: $etudiant->filiere?->nom,
        ))->all();
    }

    public function ajouterEtudiantsAtomique(int $groupeId, array $etudiantIds): void
    {
        try {
            DB::transaction(function () use ($groupeId, $etudiantIds): void {
                $groupe = EloquentGroupe::query()->lockForUpdate()->findOrFail($groupeId);
                $idsUniques = array_values(array_unique(array_map('intval', $etudiantIds)));

                $effectifActuel = (int) DB::table('etudiant_groupe')->where('groupe_id', $groupeId)->count();
                $reste = $groupe->effectif_max - $effectifActuel;
                if (count($idsUniques) > $reste) {
                    throw new CapaciteGroupeDepasseeException();
                }

                $autresGroupes = DB::table('etudiant_groupe as eg')
                    ->join('groupe as g', 'g.id', '=', 'eg.groupe_id')
                    ->whereIn('eg.etudiant_id', $idsUniques)
                    ->where('g.annee_id', $groupe->annee_id)
                    ->where('eg.groupe_id', '!=', $groupeId)
                    ->select('eg.etudiant_id')
                    ->pluck('etudiant_id')
                    ->all();

                if ($autresGroupes !== []) {
                    throw new EtudiantDejaDansUnGroupeException();
                }

                $rows = [];
                foreach ($idsUniques as $etudiantId) {
                    if (DB::table('etudiant_groupe')->where('groupe_id', $groupeId)->where('etudiant_id', $etudiantId)->exists()) {
                        continue;
                    }

                    $rows[] = [
                        'etudiant_id' => $etudiantId,
                        'groupe_id' => $groupeId,
                    ];
                }

                if ($rows !== []) {
                    DB::table('etudiant_groupe')->insert($rows);
                }
            });
        } catch (QueryException $exception) {
            DatabaseExceptionTranslator::translate($exception);
        }
    }

    public function retirerEtudiant(int $groupeId, int $etudiantId): void
    {
        try {
            DB::table('etudiant_groupe')
                ->where('groupe_id', $groupeId)
                ->where('etudiant_id', $etudiantId)
                ->delete();
        } catch (QueryException $exception) {
            DatabaseExceptionTranslator::translate($exception);
        }
    }

    public function groupeDeLEtudiantPourAnnee(int $etudiantId, int $anneeId): ?Groupe
    {
        $model = EloquentGroupe::query()
            ->join('etudiant_groupe as eg', 'eg.groupe_id', '=', 'groupe.id')
            ->where('eg.etudiant_id', $etudiantId)
            ->where('groupe.annee_id', $anneeId)
            ->select('groupe.*')
            ->first();

        return $model === null ? null : $this->toDomain($model->load(['filiere', 'anneeUniversitaire']));
    }

    private function toDomain(EloquentGroupe $model): Groupe
    {
        $effectifActuel = (int) ($model->etudiants_count ?? $model->etudiants()->count());

        return new Groupe(
            id: (int) $model->id,
            nom: (string) $model->nom,
            filiereId: (int) $model->filiere_id,
            anneeId: (int) $model->annee_id,
            effectifMax: (int) $model->effectif_max,
            effectifActuel: $effectifActuel,
        );
    }
}
