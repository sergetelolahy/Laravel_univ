<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CalculMoyennesService
{
    /**
     * Calcule la moyenne pondérée d'un étudiant pour un semestre et une année.
     * Seules les évaluations notées sont prises en compte.
     */
    public function moyenneSemestre(int $etudiantId, int $semestreId, int $anneeId): ?float
    {
        $resultat = DB::table('v_moyenne_semestre')
            ->where('etudiant_id', $etudiantId)
            ->where('semestre_id', $semestreId)
            ->where('annee_id', $anneeId)
            ->value('moyenne');

        return $resultat === null ? null : round((float) $resultat, 2);
    }

    public function validerSemestre(int $etudiantId, int $semestreId, int $anneeId): array
    {
        $moyenne = $this->moyenneSemestre($etudiantId, $semestreId, $anneeId);

        if ($moyenne === null) {
            throw ValidationException::withMessages([
                'semestre' => 'Impossible de valider : aucune note disponible pour ce semestre.',
            ]);
        }

        $valide = $moyenne >= 10.0;

        DB::table('validation_semestre')->updateOrInsert(
            [
                'etudiant_id' => $etudiantId,
                'semestre_id' => $semestreId,
                'annee_id' => $anneeId,
            ],
            [
                'moyenne' => $moyenne,
                'valide' => $valide,
                'valide_le' => $valide ? now() : null,
                'verrouille' => true,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        return [
            'etudiant_id' => $etudiantId,
            'semestre_id' => $semestreId,
            'annee_id' => $anneeId,
            'moyenne' => $moyenne,
            'valide' => $valide,
            'message' => $valide
                ? 'Semestre validé.'
                : 'Semestre non validé : la moyenne est inférieure à 10/20.',
        ];
    }
}
