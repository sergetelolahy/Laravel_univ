<?php

declare(strict_types=1);

namespace App\Infrastructure\Planning;

use App\Domain\Planning\Repositories\PlanningContexte;
use Illuminate\Support\Facades\DB;

final class SqlPlanningContexte implements PlanningContexte
{
    public function filiereDuCours(int $coursId): ?int
    {
        $v = DB::table('cours as c')
            ->join('semestre as s', 's.id', '=', 'c.semestre_id')
            ->where('c.id', $coursId)
            ->value('s.filiere_id');

        return $v === null ? null : (int) $v;
    }

    public function groupe(int $groupeId): ?array
    {
        $g = DB::table('groupe')->where('id', $groupeId)->first(['id', 'filiere_id', 'annee_id']);
        if ($g === null) {
            return null;
        }

        return [
            'id' => (int) $g->id,
            'filiere_id' => (int) $g->filiere_id,
            'annee_id' => (int) $g->annee_id,
            'effectif' => DB::table('etudiant_groupe')->where('groupe_id', $groupeId)->count(),
        ];
    }

    public function salle(int $salleId): ?array
    {
        $s = DB::table('salle')->where('id', $salleId)->first(['id', 'nom', 'capacite']);

        return $s === null ? null : ['id' => (int) $s->id, 'nom' => (string) $s->nom, 'capacite' => (int) $s->capacite];
    }

    public function enseignantAffecte(int $enseignantId, int $coursId, int $anneeId): bool
    {
        return DB::table('enseignant_cours')
            ->where('enseignant_id', $enseignantId)
            ->where('cours_id', $coursId)
            ->where('annee_id', $anneeId)
            ->exists();
    }

    public function anneePlage(int $anneeId): ?array
    {
        $a = DB::table('annee_universitaire')->where('id', $anneeId)->first(['date_debut', 'date_fin']);

        return $a === null ? null : [
            'date_debut' => substr((string) $a->date_debut, 0, 10),
            'date_fin' => substr((string) $a->date_fin, 0, 10),
        ];
    }

    public function enseignantIdPourUtilisateur(int $utilisateurId): ?int
    {
        $v = DB::table('enseignant')->where('utilisateur_id', $utilisateurId)->value('id');

        return $v === null ? null : (int) $v;
    }

    public function etudiantIdPourUtilisateur(int $utilisateurId): ?int
    {
        $v = DB::table('etudiant')->where('utilisateur_id', $utilisateurId)->value('id');

        return $v === null ? null : (int) $v;
    }
}
