<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Application\Referentiel\Salle\CreerSalle;
use App\Application\Referentiel\Salle\ListerSalles;
use App\Application\Referentiel\Salle\ModifierSalle;
use App\Application\Referentiel\Salle\ObtenirSalle;
use App\Application\Referentiel\Salle\SupprimerSalle;
use App\Http\Requests\StoreSalleRequest;
use App\Http\Requests\UpdateSalleRequest;
use App\Http\Resources\SalleResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

final class SalleController extends Controller
{
    public function __construct(
        private readonly CreerSalle $creerSalle,
        private readonly ListerSalles $listerSalles,
        private readonly ObtenirSalle $obtenirSalle,
        private readonly ModifierSalle $modifierSalle,
        private readonly SupprimerSalle $supprimerSalle,
    ) {
    }

    public function index(): JsonResponse
    {
        $result = $this->listerSalles->execute(request()->query->all(), 1, 15);

        return response()->json([
            'data' => SalleResource::collection(collect($result)),
        ], 200);
    }

    public function show(string $id): JsonResponse
    {
        abort_unless(ctype_digit($id), 404);

        $salle = $this->obtenirSalle->execute((int) $id);

        return response()->json([
            'data' => new SalleResource($salle),
        ], 200);
    }

    public function store(StoreSalleRequest $request): JsonResponse
    {
        $salle = $this->creerSalle->execute($request->validated());

        return response()->json([
            'data' => new SalleResource($salle),
        ], 201);
    }

    public function update(int $id, UpdateSalleRequest $request): JsonResponse
    {
        $salle = $this->modifierSalle->execute($id, $request->validated());

        return response()->json([
            'data' => new SalleResource($salle),
        ], 200);
    }

    public function destroy(int $id): JsonResponse
    {
        $this->supprimerSalle->execute($id);

        return response()->json(null, 204);
    }
}
