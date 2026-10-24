<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Evaluation;
use App\Models\Utilisateur;
use Illuminate\Support\Facades\DB;

final class EvaluationPolicy
{
    public function saisirNotes(Utilisateur $user, Evaluation $evaluation): bool
    {
        $enseignantId = $user->enseignant?->id;
        if (! $enseignantId) {
            return false;
        }

        return DB::table('enseignant_cours')
            ->where('enseignant_id', $enseignantId)
            ->where('cours_id', $evaluation->cours_id)
            ->where('annee_id', $evaluation->annee_id)
            ->exists();
    }
}
