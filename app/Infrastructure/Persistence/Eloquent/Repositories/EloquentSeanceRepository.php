<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\Planning\Entities\PageSeances;
use App\Domain\Planning\Entities\SalleDisponible;
use App\Domain\Planning\Entities\Seance;
use App\Domain\Planning\Exceptions\CapaciteSalleInsuffisanteException;
use App\Domain\Planning\Exceptions\CreneauInvalideException;
use App\Domain\Planning\Exceptions\ElementIntrouvableException;
use App\Domain\Planning\Exceptions\EnseignantOccupeException;
use App\Domain\Planning\Exceptions\GroupeOccupeException;
use App\Domain\Planning\Exceptions\ReferenceIntrouvableException;
use App\Domain\Planning\Exceptions\SalleOccupeeException;
use App\Domain\Planning\Exceptions\SeanceUtiliseeException;
use App\Domain\Planning\Repositories\SeanceRepository;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

/**
 * Implémentation MySQL (query builder avec jointures : une seule requête, pas de N+1).
 * Détection des conflits : verrous sur les lignes parent + triggers MySQL (filet de sécurité).
 */
final class EloquentSeanceRepository implements SeanceRepository
{
    public function creer(array $donnees): Seance
    {
        $id = (int) $this->executer(
            $donnees,
            fn () => DB::table('seance')->insertGetId($this->colonnes($donnees))
        );

        return $this->trouverParId($id)
            ?? throw new ElementIntrouvableException('Séance introuvable après création.');
    }

    public function modifier(int $id, array $donnees): Seance
    {
        $this->executer(
            $donnees,
            fn () => DB::table('seance')->where('id', $id)->update($this->colonnes($donnees))
        );

        return $this->trouverParId($id)
            ?? throw new ElementIntrouvableException('Séance introuvable.');
    }

    public function supprimer(int $id): void
    {
        try {
            DB::table('seance')->where('id', $id)->delete();
        } catch (QueryException $e) {
            throw $this->traduire($e);
        }
    }

    public function trouverParId(int $id): ?Seance
    {
        $ligne = $this->base()->where('s.id', $id)->first();

        return $ligne === null ? null : $this->versEntite($ligne);
    }

    public function lister(array $filtres, int $page, int $parPage): PageSeances
    {
        $requete = $this->base();
        foreach (['groupe_id' => 's.groupe_id', 'enseignant_id' => 's.enseignant_id', 'salle_id' => 's.salle_id', 'cours_id' => 's.cours_id'] as $cle => $colonne) {
            if (isset($filtres[$cle])) {
                $requete->where($colonne, (int) $filtres[$cle]);
            }
        }
        if (isset($filtres['du'])) {
            $requete->where('s.debut', '>=', $filtres['du'] . ' 00:00:00');
        }
        if (isset($filtres['au'])) {
            $requete->where('s.debut', '<=', $filtres['au'] . ' 23:59:59');
        }

        $total = (clone $requete)->count();
        $lignes = $requete->orderBy('s.debut')->orderBy('s.id')->forPage($page, $parPage)->get();

        return new PageSeances($lignes->map(fn ($l) => $this->versEntite($l))->all(), $total);
    }

    public function listerPourEnseignant(int $enseignantId, string $du, string $au): array
    {
        return $this->periode($this->base()->where('s.enseignant_id', $enseignantId), $du, $au);
    }

    public function listerPourEtudiant(int $etudiantId, string $du, string $au): array
    {
        $requete = $this->base()->whereIn('s.groupe_id', function ($q) use ($etudiantId) {
            $q->select('groupe_id')->from('etudiant_groupe')->where('etudiant_id', $etudiantId);
        });

        return $this->periode($requete, $du, $au);
    }

    public function occupationSalle(int $salleId, string $du, string $au): array
    {
        return $this->periode($this->base()->where('s.salle_id', $salleId), $du, $au);
    }

    public function sallesDisponibles(string $debut, string $fin, int $capaciteMin): array
    {
        return DB::table('salle as sa')
            ->where('sa.capacite', '>=', $capaciteMin)
            ->whereNotExists(function ($q) use ($debut, $fin) {
                $q->select(DB::raw(1))
                    ->from('seance as s')
                    ->whereColumn('s.salle_id', 'sa.id')
                    ->where('s.debut', '<', $fin)
                    ->where('s.fin', '>', $debut);
            })
            ->orderBy('sa.capacite')
            ->orderBy('sa.nom')
            ->get(['sa.id', 'sa.nom', 'sa.capacite'])
            ->map(fn ($s) => new SalleDisponible((int) $s->id, (string) $s->nom, (int) $s->capacite))
            ->all();
    }

    // ------------------------------------------------------------------ interne

    private function base(): Builder
    {
        return DB::table('seance as s')
            ->join('cours as c', 'c.id', '=', 's.cours_id')
            ->join('groupe as g', 'g.id', '=', 's.groupe_id')
            ->join('enseignant as e', 'e.id', '=', 's.enseignant_id')
            ->join('salle as sa', 'sa.id', '=', 's.salle_id')
            ->select(
                's.id', 's.debut', 's.fin',
                'c.id as cours_id', 'c.code as cours_code', 'c.intitule as cours_intitule',
                'g.id as groupe_id', 'g.nom as groupe_nom',
                'e.id as enseignant_id', 'e.nom as enseignant_nom', 'e.prenom as enseignant_prenom',
                'sa.id as salle_id', 'sa.nom as salle_nom', 'sa.capacite as salle_capacite'
            );
    }

    /** @return Seance[] */
    private function periode(Builder $requete, string $du, string $au): array
    {
        return $requete
            ->where('s.debut', '>=', $du . ' 00:00:00')
            ->where('s.debut', '<=', $au . ' 23:59:59')
            ->orderBy('s.debut')->orderBy('s.id')
            ->get()
            ->map(fn ($l) => $this->versEntite($l))
            ->all();
    }

    private function versEntite(object $r): Seance
    {
        return new Seance(
            (int) $r->id,
            new \DateTimeImmutable((string) $r->debut),
            new \DateTimeImmutable((string) $r->fin),
            ['id' => (int) $r->cours_id, 'code' => (string) $r->cours_code, 'intitule' => (string) $r->cours_intitule],
            ['id' => (int) $r->groupe_id, 'nom' => (string) $r->groupe_nom],
            ['id' => (int) $r->enseignant_id, 'nom' => (string) $r->enseignant_nom, 'prenom' => (string) $r->enseignant_prenom],
            ['id' => (int) $r->salle_id, 'nom' => (string) $r->salle_nom, 'capacite' => (int) $r->salle_capacite],
        );
    }

    /** @return array<string,mixed> */
    private function colonnes(array $d): array
    {
        return [
            'cours_id' => $d['cours_id'],
            'groupe_id' => $d['groupe_id'],
            'enseignant_id' => $d['enseignant_id'],
            'salle_id' => $d['salle_id'],
            'debut' => $d['debut'],
            'fin' => $d['fin'],
        ];
    }

    /**
     * Transaction avec verrous sur les lignes parent, toujours dans le même ordre
     * (enseignant, groupe, salle) pour éviter les deadlocks. Deux planifications
     * concurrentes sur la même ressource sont ainsi sérialisées.
     */
    private function executer(array $d, callable $operation): mixed
    {
        try {
            return DB::transaction(function () use ($d, $operation) {
                DB::table('enseignant')->where('id', $d['enseignant_id'])->lockForUpdate()->first();
                DB::table('groupe')->where('id', $d['groupe_id'])->lockForUpdate()->first();
                DB::table('salle')->where('id', $d['salle_id'])->lockForUpdate()->first();

                return $operation();
            }, 3);
        } catch (QueryException $e) {
            throw $this->traduire($e);
        }
    }

    private function traduire(QueryException $e): \Throwable
    {
        return match ((int) ($e->errorInfo[1] ?? 0)) {
            5001 => new SalleOccupeeException(),
            5002 => new EnseignantOccupeException(),
            5003 => new GroupeOccupeException(),
            5004 => new CapaciteSalleInsuffisanteException(),
            1451 => new SeanceUtiliseeException(),
            1452 => new ReferenceIntrouvableException(),
            3819, 4025 => new CreneauInvalideException(),
            default => $e,
        };
    }
}
