<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreNotesRequest;
use App\Models\Evaluation;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class NoteController extends Controller
{
    public function storeBatch(StoreNotesRequest $request, Evaluation $evaluation): JsonResponse
    {
        $enseignantId = $request->user()->enseignant?->id;
        abort_unless($enseignantId, 403, 'Aucun profil enseignant associé à ce compte.');

        $affecte = DB::table('enseignant_cours')
            ->where('enseignant_id', $enseignantId)
            ->where('cours_id', $evaluation->cours_id)
            ->where('annee_id', $evaluation->annee_id)
            ->exists();

        abort_unless($affecte, 403, 'Vous ne pouvez saisir les notes que pour vos propres cours.');

        $notes = $request->validated('notes');

        // Vérifie avant écriture que chaque étudiant est inscrit au cours et à l'année de l'évaluation.
        $ids = array_column($notes, 'etudiant_id');
        $inscrits = DB::table('inscription_cours')
            ->where('cours_id', $evaluation->cours_id)
            ->where('annee_id', $evaluation->annee_id)
            ->whereIn('etudiant_id', $ids)
            ->pluck('etudiant_id')
            ->map(static fn ($id) => (int) $id)
            ->all();

        $nonInscrits = array_values(array_diff(array_map('intval', $ids), $inscrits));
        if ($nonInscrits !== []) {
            throw ValidationException::withMessages([
                'notes' => ['Certains étudiants ne sont pas inscrits à ce cours pour cette année : '.implode(', ', $nonInscrits)],
            ]);
        }

        // Une note est refusée si le semestre correspondant est verrouillé pour l'étudiant.
        $semestreId = DB::table('cours')->where('id', $evaluation->cours_id)->value('semestre_id');
        $verrouille = DB::table('validation_semestre')
            ->whereIn('etudiant_id', $ids)
            ->where('semestre_id', $semestreId)
            ->where('annee_id', $evaluation->annee_id)
            ->where('verrouille', true)
            ->exists();

        abort_if($verrouille, 423, 'Notes verrouillées : le semestre est validé pour au moins un étudiant.');

        DB::transaction(function () use ($notes, $evaluation, $enseignantId): void {
            $now = now();
            foreach ($notes as $ligne) {
                DB::table('note')->updateOrInsert(
                    [
                        'evaluation_id' => $evaluation->id,
                        'etudiant_id' => $ligne['etudiant_id'],
                    ],
                    [
                        'valeur' => $ligne['valeur'],
                        'saisie_par' => $enseignantId,
                        'date_saisie' => $now,
                    ]
                );
            }
        });

        return response()->json([
            'message' => 'Notes enregistrées avec succès.',
            'nombre' => count($notes),
        ], 201);
    }
}
