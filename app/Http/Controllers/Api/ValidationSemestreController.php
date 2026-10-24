<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\CalculMoyennesService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ValidationSemestreController extends Controller
{
    public function store(Request $request, int $semestre, CalculMoyennesService $calcul)
    {
        $role = strtolower((string) ($request->user()->role ?? ''));

        abort_unless(in_array($role, ['admin', 'administrateur', 'scolarite', 'scolarité'], true),
            403, 'Seuls l’administrateur ou la scolarité peuvent valider un semestre.');

        $data = $request->validate([
            'etudiant_id' => ['required', 'integer', 'exists:etudiant,id'],
            'annee_id' => ['required', 'integer', 'exists:annee_universitaire,id'],
        ]);

        abort_unless(DB::table('semestre')->where('id', $semestre)->exists(), 404, 'Semestre introuvable.');

        $resultat = $calcul->validerSemestre(
            (int) $data['etudiant_id'],
            $semestre,
            (int) $data['annee_id']
        );

        return response()->json($resultat);
    }
}
