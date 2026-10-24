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
        $resultat = DB::table('evaluation as ev')
            ->join('cours as c', 'c.id', '=', 'ev.cours_id')
            ->join('note as n', 'n.evaluation_id', '=', 'ev.id')
            ->where('n.etudiant_id', $etudiantId)
            ->where('c.semestre_id', $semestreId)
            ->whereExists(function ($query) use ($etudiantId, $anneeId) {
                $query->selectRaw('1')
                    ->from('inscription_cours as ic')
                    ->whereColumn('ic.cours_id', 'c.id')
                    ->where('ic.etudiant_id', $etudiantId)
                    ->where('ic.annee_id', $anneeId);
            })
            ->selectRaw('SUM(n.valeur * ev.poids) / NULLIF(SUM(ev.poids), 0) AS moyenne')
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
