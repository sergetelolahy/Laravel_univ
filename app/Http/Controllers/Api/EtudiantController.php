<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Application\Scolarite\Etudiants\ListerEtudiants;
use App\Application\Scolarite\Etudiants\ModifierEtudiant;
use App\Application\Scolarite\Etudiants\ObtenirEtudiant;
use App\Application\Scolarite\Etudiants\ObtenirMaFiche;
use App\Application\Scolarite\Inscriptions\ListerMesInscriptions;
use App\Http\Requests\UpdateEtudiantRequest;
use App\Http\Resources\EtudiantResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

final class EtudiantController extends Controller
{
    public function __construct(
        private readonly ListerEtudiants $listerEtudiants,
        private readonly ObtenirEtudiant $obtenirEtudiant,
        private readonly ModifierEtudiant $modifierEtudiant,
        private readonly ObtenirMaFiche $obtenirMaFiche,
        private readonly ListerMesInscriptions $listerMesInscriptions,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $filiereId = $request->query('filiere_id');
        $q = $request->query('q');

        $result = $this->listerEtudiants->execute(
            $filiereId !== null ? (int) $filiereId : null,
            $q !== null ? (string) $q : null,
            (int) ($request->query('page', 1)),
            (int) ($request->query('per_page', 20)),
        );

        return response()->json([
            'message' => 'Étudiants récupérés avec succès.',
            'data' => EtudiantResource::collection($result),
        ], 200);
    }

    public function me(Request $request): JsonResponse
    {
        $etudiant = $this->obtenirMaFiche->execute((int) $request->user()->id);

        return response()->json([
            'message' => 'Fiche étudiant récupérée avec succès.',
            'data' => new EtudiantResource($etudiant),
        ], 200);
    }

    public function mesInscriptions(Request $request): JsonResponse
    {
        $inscriptions = $this->listerMesInscriptions->execute((int) $request->user()->id);

        return response()->json([
            'message' => 'Mes inscriptions récupérées avec succès.',
            'data' => $inscriptions,
        ], 200);
    }

    public function show(int $id): JsonResponse
    {
        $etudiant = $this->obtenirEtudiant->execute($id);

        return response()->json([
            'message' => 'Étudiant récupéré avec succès.',
            'data' => new EtudiantResource($etudiant),
        ], 200);
    }

    public function update(int $id, UpdateEtudiantRequest $request): JsonResponse
    {
        $etudiant = $this->modifierEtudiant->execute($id, $request->validated());

        return response()->json([
            'message' => 'Étudiant modifié avec succès.',
            'data' => new EtudiantResource($etudiant),
        ], 200);
    }

}
