<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Application\Referentiel\Cours\AffecterEnseignantACours;
use App\Application\Referentiel\Cours\CreerCours;
use App\Application\Referentiel\Cours\ListerCours;
use App\Application\Referentiel\Cours\ListerEnseignantsDuCours;
use App\Application\Referentiel\Cours\ModifierCours;
use App\Application\Referentiel\Cours\ObtenirCours;
use App\Application\Referentiel\Cours\RetirerEnseignantDuCours;
use App\Application\Referentiel\Cours\SupprimerCours;
use App\Application\Scolarite\Inscriptions\ListerInscritsDuCours;
use App\Http\Requests\StoreCoursRequest;
use App\Http\Requests\UpdateCoursRequest;
use App\Http\Resources\CoursResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

final class CoursController extends Controller
{
    public function __construct(
        private readonly CreerCours $creerCours,
        private readonly ListerCours $listerCours,
        private readonly ObtenirCours $obtenirCours,
        private readonly ModifierCours $modifierCours,
        private readonly SupprimerCours $supprimerCours,
        private readonly AffecterEnseignantACours $affecterEnseignant,
        private readonly RetirerEnseignantDuCours $retirerEnseignant,
        private readonly ListerEnseignantsDuCours $listerEnseignants,
        private readonly ListerInscritsDuCours $listerInscritsDuCours,
    ) {
    }

    public function index(): JsonResponse
    {
        $result = $this->listerCours->execute(request()->query->all(), 1, 15);

        return response()->json([
            'data' => CoursResource::collection(collect($result)),
        ], 200);
    }

    public function show(int $id): JsonResponse
    {
        $cours = $this->obtenirCours->execute($id);

        return response()->json([
            'data' => new CoursResource($cours),
        ], 200);
    }

    public function store(StoreCoursRequest $request): JsonResponse
    {
        $cours = $this->creerCours->execute($request->validated());

        return response()->json([
            'data' => new CoursResource($cours),
        ], 201);
    }

    public function update(int $id, UpdateCoursRequest $request): JsonResponse
    {
        $cours = $this->modifierCours->execute($id, $request->validated());

        return response()->json([
            'data' => new CoursResource($cours),
        ], 200);
    }

    public function destroy(int $id): JsonResponse
    {
        $this->supprimerCours->execute($id);

        return response()->json(null, 204);
    }

    public function attachEnseignant(int $id): JsonResponse
    {
        $donnees = request()->validate([
            'enseignant_id' => ['required', 'integer', 'gt:0'],
            'annee_id' => ['required', 'integer', 'gt:0'],
        ], [
            'enseignant_id.required' => 'L’enseignant est obligatoire.',
            'annee_id.required' => 'L’année est obligatoire.',
        ]);

        $this->affecterEnseignant->execute($id, (int) $donnees['enseignant_id'], (int) $donnees['annee_id']);

        return response()->json(['message' => 'L’enseignant a été affecté au cours.'], 200);
    }

    public function detachEnseignant(int $id, int $enseignantId): JsonResponse
    {
        $donnees = request()->validate([
            'annee_id' => ['required', 'integer', 'gt:0'],
        ], [
            'annee_id.required' => 'L’année est obligatoire.',
        ]);

        $this->retirerEnseignant->execute($id, $enseignantId, (int) $donnees['annee_id']);

        return response()->json(['message' => 'L’enseignant a été retiré du cours.'], 200);
    }

    public function enseignants(int $id): JsonResponse
    {
        $anneeId = request()->query('annee_id');
        $result = $this->listerEnseignants->execute($id, $anneeId !== null ? (int) $anneeId : null);

        return response()->json([
            'data' => $result,
        ], 200);
    }

    public function inscritsDuCours(int $id, Request $request): JsonResponse
    {
        $anneeId = $request->query('annee_id');
        $result = $this->listerInscritsDuCours->execute(
            $id,
            $anneeId !== null ? (int) $anneeId : null,
            $request->user() ? (int) $request->user()->id : null,
            $request->user() ? (string) $request->user()->role->value : null,
        );

        return response()->json([
            'message' => 'Liste des inscrits récupérée avec succès.',
            'data' => $result,
        ], 200);
    }
}
