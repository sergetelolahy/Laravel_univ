<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Application\Referentiel\Semestre\CreerSemestre;
use App\Application\Referentiel\Semestre\ListerSemestres;
use App\Application\Referentiel\Semestre\ModifierSemestre;
use App\Application\Referentiel\Semestre\ObtenirSemestre;
use App\Application\Referentiel\Semestre\SupprimerSemestre;
use App\Http\Requests\StoreSemestreRequest;
use App\Http\Requests\UpdateSemestreRequest;
use App\Http\Resources\SemestreResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

final class SemestreController extends Controller
{
    public function __construct(
        private readonly CreerSemestre $creerSemestre,
        private readonly ListerSemestres $listerSemestres,
        private readonly ObtenirSemestre $obtenirSemestre,
        private readonly ModifierSemestre $modifierSemestre,
        private readonly SupprimerSemestre $supprimerSemestre,
    ) {
    }

    public function index(): JsonResponse
    {
        $result = $this->listerSemestres->execute(request()->query->all(), 1, 15);

        return response()->json([
            'data' => SemestreResource::collection(collect($result)),
        ], 200);
    }

    public function show(int $id): JsonResponse
    {
        $semestre = $this->obtenirSemestre->execute($id);

        return response()->json([
            'data' => new SemestreResource($semestre),
        ], 200);
    }

    public function store(StoreSemestreRequest $request): JsonResponse
    {
        $semestre = $this->creerSemestre->execute($request->validated());

        return response()->json([
            'data' => new SemestreResource($semestre),
        ], 201);
    }

    public function update(int $id, UpdateSemestreRequest $request): JsonResponse
    {
        $semestre = $this->modifierSemestre->execute($id, $request->validated());

        return response()->json([
            'data' => new SemestreResource($semestre),
        ], 200);
    }

    public function destroy(int $id): JsonResponse
    {
        $this->supprimerSemestre->execute($id);

        return response()->json(null, 204);
    }
}
