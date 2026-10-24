<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEvaluationRequest;
use App\Models\Evaluation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

final class EvaluationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Evaluation::query()->orderByDesc('id');

        if ($request->filled('cours_id')) {
            $query->where('cours_id', $request->integer('cours_id'));
        }
        if ($request->filled('annee_id')) {
            $query->where('annee_id', $request->integer('annee_id'));
        }

        return response()->json([
            'data' => $query->paginate(min(max($request->integer('per_page', 15), 1), 100)),
        ]);
    }

    public function store(StoreEvaluationRequest $request): JsonResponse
    {
        $data = $request->validated();

        // L'enseignant ne peut créer une évaluation que pour un cours qui lui est affecté.
        if ($request->user()->role?->value === 'ENSEIGNANT' || $request->user()->role === 'ENSEIGNANT') {
            $enseignantId = $request->user()->enseignant?->id;
            abort_unless($enseignantId, 403, 'Aucun profil enseignant associé à ce compte.');

            $affecte = DB::table('enseignant_cours')
                ->where('enseignant_id', $enseignantId)
                ->where('cours_id', $data['cours_id'])
                ->where('annee_id', $data['annee_id'])
                ->exists();

            abort_unless($affecte, 403, 'Vous ne pouvez créer une évaluation que pour vos cours.');
        }

        $evaluation = Evaluation::query()->create($data);

        return response()->json(['data' => $evaluation], 201);
    }
}
