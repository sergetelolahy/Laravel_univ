<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\Scolarite\Repositories\EnseignementRepository;
use Illuminate\Support\Facades\DB;

final class EloquentEnseignementRepository implements EnseignementRepository
{
    public function enseignantAffecteAuCours(int $utilisateurId, int $coursId, int $anneeId): bool
    {
        return DB::table('enseignant_cours as ec')
            ->join('enseignant as e', 'e.id', '=', 'ec.enseignant_id')
            ->where('ec.cours_id', $coursId)
            ->where('ec.annee_id', $anneeId)
            ->where('e.utilisateur_id', $utilisateurId)
            ->exists();
    }

    public function filierePourCours(int $coursId): int
    {
        $filiereId = DB::table('cours as c')
            ->join('semestre as s', 's.id', '=', 'c.semestre_id')
            ->where('c.id', $coursId)
            ->value('s.filiere_id');

        return $filiereId === null ? 0 : (int) $filiereId;
    }

    public function anneeCourante(): ?int
    {
        $anneeId = DB::table('annee_universitaire')
            ->where('courante', true)
            ->value('id');

        return $anneeId === null ? null : (int) $anneeId;
    }
}
