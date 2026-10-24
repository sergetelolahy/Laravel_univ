<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Application\Referentiel\Filiere\CreerFiliere;
use App\Application\Referentiel\Filiere\ListerFilieres;
use App\Application\Referentiel\Filiere\ModifierFiliere;
use App\Application\Referentiel\Filiere\ObtenirFiliere;
use App\Application\Referentiel\Filiere\SupprimerFiliere;
use App\Http\Requests\StoreFiliereRequest;
use App\Http\Requests\UpdateFiliereRequest;
use App\Http\Resources\FiliereResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

final class FiliereController extends Controller
{
    public function __construct(
        private readonly CreerFiliere $creerFiliere,
        private readonly ListerFilieres $listerFilieres,
        private readonly ObtenirFiliere $obtenirFiliere,
        private readonly ModifierFiliere $modifierFiliere,
        private readonly SupprimerFiliere $supprimerFiliere,
    ) {
    }

    public function index(): JsonResponse
    {
        $result = $this->listerFilieres->execute(request()->query->all(), 1, 15);

        return response()->json([
            'data' => FiliereResource::collection(collect($result)),
        ], 200);
    }

    public function show(int $id): JsonResponse
    {
        $filiere = $this->obtenirFiliere->execute($id);

        return response()->json([
            'data' => new FiliereResource($filiere),
        ], 200);
    }

    public function store(StoreFiliereRequest $request): JsonResponse
    {
        $filiere = $this->creerFiliere->execute($request->validated());

        return response()->json([
            'data' => new FiliereResource($filiere),
        ], 201);
    }

    public function update(int $id, UpdateFiliereRequest $request): JsonResponse
    {
        $filiere = $this->modifierFiliere->execute($id, $request->validated());

        return response()->json([
            'data' => new FiliereResource($filiere),
        ], 200);
    }

    public function destroy(int $id): JsonResponse
    {
        $this->supprimerFiliere->execute($id);

        return response()->json(null, 204);
    }
}
