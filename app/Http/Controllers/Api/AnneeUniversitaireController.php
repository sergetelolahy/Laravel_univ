<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Application\Referentiel\AnneeUniversitaire\CreerAnneeUniversitaire;
use App\Application\Referentiel\AnneeUniversitaire\DefinirAnneeCourante;
use App\Application\Referentiel\AnneeUniversitaire\ListerAnneesUniversitaires;
use App\Application\Referentiel\AnneeUniversitaire\ModifierAnneeUniversitaire;
use App\Application\Referentiel\AnneeUniversitaire\ObtenirAnneeUniversitaire;
use App\Application\Referentiel\AnneeUniversitaire\SupprimerAnneeUniversitaire;
use App\Http\Requests\StoreAnneeUniversitaireRequest;
use App\Http\Requests\UpdateAnneeUniversitaireRequest;
use App\Http\Resources\AnneeUniversitaireResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

final class AnneeUniversitaireController extends Controller
{
    public function __construct(
        private readonly CreerAnneeUniversitaire $creerAnnee,
        private readonly ListerAnneesUniversitaires $listerAnnees,
        private readonly ObtenirAnneeUniversitaire $obtenirAnnee,
        private readonly ModifierAnneeUniversitaire $modifierAnnee,
        private readonly SupprimerAnneeUniversitaire $supprimerAnnee,
        private readonly DefinirAnneeCourante $definirAnneeCourante,
    ) {
    }

    public function index(): JsonResponse
    {
        $result = $this->listerAnnees->execute(request()->query->all(), 1, 15);

        return response()->json([
            'data' => AnneeUniversitaireResource::collection(collect($result)),
        ], 200);
    }

    public function courante(): JsonResponse
    {
        $annees = $this->listerAnnees->execute(['courante' => true], 1, 15);
        if ($annees === []) {
            return response()->json(['message' => 'Aucune année universitaire courante.'], 404);
        }

        return response()->json([
            'data' => new AnneeUniversitaireResource($annees[0]),
        ], 200);
    }

    public function show(int $id): JsonResponse
    {
        $annee = $this->obtenirAnnee->execute($id);

        return response()->json([
            'data' => new AnneeUniversitaireResource($annee),
        ], 200);
    }

    public function store(StoreAnneeUniversitaireRequest $request): JsonResponse
    {
        $annee = $this->creerAnnee->execute($request->validated());

        return response()->json([
            'data' => new AnneeUniversitaireResource($annee),
        ], 201);
    }

    public function update(int $id, UpdateAnneeUniversitaireRequest $request): JsonResponse
    {
        $annee = $this->modifierAnnee->execute($id, $request->validated());

        return response()->json([
            'data' => new AnneeUniversitaireResource($annee),
        ], 200);
    }

    public function destroy(int $id): JsonResponse
    {
        $this->supprimerAnnee->execute($id);

        return response()->json(null, 204);
    }

    public function setCourante(int $id): JsonResponse
    {
        $this->definirAnneeCourante->execute($id);

        return response()->json(['message' => 'L’année courante a été définie.'], 200);
    }
}
