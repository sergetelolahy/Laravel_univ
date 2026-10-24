<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Application\Scolarite\Inscriptions\Desinscrire;
use App\Application\Scolarite\Inscriptions\InscrireAUnCours;
use App\Application\Scolarite\Inscriptions\InscrireEnLot;
use App\Application\Scolarite\Inscriptions\ListerInscriptions;
use App\Application\Scolarite\Inscriptions\ListerInscritsDuCours;
use App\Http\Requests\StoreInscriptionRequest;
use App\Http\Requests\StoreLotInscriptionsRequest;
use App\Http\Resources\InscriptionResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

final class InscriptionController extends Controller
{
    public function __construct(
        private readonly ListerInscriptions $listerInscriptions,
        private readonly InscrireAUnCours $inscrireAUnCours,
        private readonly InscrireEnLot $inscrireEnLot,
        private readonly Desinscrire $desinscrire,
        private readonly ListerInscritsDuCours $listerInscritsDuCours,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $result = $this->listerInscriptions->execute(
            $request->query('etudiant_id') !== null ? (int) $request->query('etudiant_id') : null,
            $request->query('cours_id') !== null ? (int) $request->query('cours_id') : null,
            $request->query('annee_id') !== null ? (int) $request->query('annee_id') : null,
            $request->query('filiere_id') !== null ? (int) $request->query('filiere_id') : null,
            (int) ($request->query('page', 1)),
            (int) ($request->query('per_page', 20)),
        );

        return response()->json([
            'message' => 'Inscriptions récupérées avec succès.',
            'data' => InscriptionResource::collection($result),
        ], 200);
    }

    public function store(StoreInscriptionRequest $request): JsonResponse
    {
        $donnees = $request->validated();
        $donnees['utilisateur_id'] = $request->user()->id;
        $donnees['role'] = $request->user()->role->value;

        $inscription = $this->inscrireAUnCours->execute($donnees);
        

        return response()->json([
            'message' => 'Inscription créée avec succès.',
            'data' => new InscriptionResource($inscription),
        ], 201);
    }

    public function storeLot(StoreLotInscriptionsRequest $request): JsonResponse
    {
        $donnees = $request->validated();
        $donnees['utilisateur_id'] = $request->user()->id;
        $donnees['role'] = $request->user()->role->value;

        $result = $this->inscrireEnLot->execute($donnees);

        return response()->json([
            'message' => 'Inscriptions créées avec succès.',
            'data' => $result,
        ], 201);
    }

    public function destroy(int $id): JsonResponse
    {
        $this->desinscrire->execute($id);

        return response()->json([
            'message' => 'Inscription supprimée avec succès.',
        ], 200);
    }

    public function inscritsDuCours(int $id, Request $request): JsonResponse
    {
        $anneeId = $request->query('annee_id');

        $result = $this->listerInscritsDuCours->execute(
            $id,
            $anneeId !== null ? (int) $anneeId : null,
        );

        return response()->json([
            'message' => 'Liste des inscrits récupérée avec succès.',
            'data' => $result,
        ], 200);
    }
}
