<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAbsencesRequest;
use App\Models\Seance;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class AbsenceController extends Controller
{
    public function storeBatch(StoreAbsencesRequest $request, Seance $seance): JsonResponse
    {
        $user = $request->user();
        $role = $user->role?->value ?? $user->role;

        if ($role === 'ENSEIGNANT') {
            $enseignantId = $user->enseignant?->id;
            abort_unless($enseignantId && (int) $seance->enseignant_id === (int) $enseignantId,
                403, 'Vous ne pouvez déclarer les absences que pour vos propres séances.');
        }

        $lignes = $request->validated('absences');
        $ids = array_column($lignes, 'etudiant_id');

        // Seuls les étudiants affectés au groupe de la séance peuvent être marqués absents.
        $membres = DB::table('etudiant_groupe')
            ->where('groupe_id', $seance->groupe_id)
            ->whereIn('etudiant_id', $ids)
            ->pluck('etudiant_id')
            ->map(static fn ($id) => (int) $id)
            ->all();

        $horsGroupe = array_values(array_diff(array_map('intval', $ids), $membres));
        if ($horsGroupe !== []) {
            throw ValidationException::withMessages([
                'absences' => ['Ces étudiants ne font pas partie du groupe de cette séance : '.implode(', ', $horsGroupe)],
            ]);
        }

        DB::transaction(function () use ($lignes, $seance): void {
            foreach ($lignes as $ligne) {
                DB::table('absence')->updateOrInsert(
                    [
                        'seance_id' => $seance->id,
                        'etudiant_id' => $ligne['etudiant_id'],
                    ],
                    ['justifiee' => $ligne['justifiee'] ?? false]
                );
            }
        });

        return response()->json([
            'message' => 'Absences enregistrées avec succès.',
            'nombre' => count($lignes),
        ], 201);
    }
}
