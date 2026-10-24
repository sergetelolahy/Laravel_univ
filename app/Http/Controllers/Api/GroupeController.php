<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Application\Scolarite\Groupes\AjouterEtudiantsAuGroupe;
use App\Application\Scolarite\Groupes\CreerGroupe;
use App\Application\Scolarite\Groupes\ListerEtudiantsDuGroupe;
use App\Application\Scolarite\Groupes\ListerGroupes;
use App\Application\Scolarite\Groupes\ModifierGroupe;
use App\Application\Scolarite\Groupes\ObtenirGroupe;
use App\Application\Scolarite\Groupes\RetirerEtudiantDuGroupe;
use App\Application\Scolarite\Groupes\SupprimerGroupe;
use App\Http\Requests\AddEtudiantsToGroupeRequest;
use App\Http\Requests\StoreGroupeRequest;
use App\Http\Requests\UpdateGroupeRequest;
use App\Http\Resources\GroupeResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

final class GroupeController extends Controller
{
    public function __construct(
        private readonly ListerGroupes $listerGroupes,
        private readonly ObtenirGroupe $obtenirGroupe,
        private readonly CreerGroupe $creerGroupe,
        private readonly ModifierGroupe $modifierGroupe,
        private readonly SupprimerGroupe $supprimerGroupe,
        private readonly ListerEtudiantsDuGroupe $listerEtudiantsDuGroupe,
        private readonly AjouterEtudiantsAuGroupe $ajouterEtudiantsAuGroupe,
        private readonly RetirerEtudiantDuGroupe $retirerEtudiantDuGroupe,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $result = $this->listerGroupes->execute(
            $request->query('filiere_id') !== null ? (int) $request->query('filiere_id') : null,
            $request->query('annee_id') !== null ? (int) $request->query('annee_id') : null,
            $request->query('q') !== null ? (string) $request->query('q') : null,
            (int) ($request->query('page', 1)),
            (int) ($request->query('per_page', 20)),
        );

        return response()->json([
            'message' => 'Groupes récupérés avec succès.',
            'data' => GroupeResource::collection($result),
        ], 200);
    }

    public function show(int $id): JsonResponse
    {
        $groupe = $this->obtenirGroupe->execute($id);

        return response()->json([
            'message' => 'Groupe récupéré avec succès.',
            'data' => new GroupeResource($groupe),
        ], 200);
    }

    public function store(StoreGroupeRequest $request): JsonResponse
    {
        $groupe = $this->creerGroupe->execute($request->validated());

        return response()->json([
            'message' => 'Groupe créé avec succès.',
            'data' => new GroupeResource($groupe),
        ], 201);
    }

    public function update(int $id, UpdateGroupeRequest $request): JsonResponse
    {
        $groupe = $this->modifierGroupe->execute($id, $request->validated());

        return response()->json([
            'message' => 'Groupe modifié avec succès.',
            'data' => new GroupeResource($groupe),
        ], 200);
    }

    public function destroy(int $id): JsonResponse
    {
        $this->supprimerGroupe->execute($id);

        return response()->json([
            'message' => 'Groupe supprimé avec succès.',
        ], 200);
    }

    public function etudiants(int $id): JsonResponse
    {
        $etudiants = $this->listerEtudiantsDuGroupe->execute($id);

        return response()->json([
            'message' => 'Étudiants du groupe récupérés avec succès.',
            'data' => $etudiants,
        ], 200);
    }

    public function ajouterEtudiants(int $id, AddEtudiantsToGroupeRequest $request): JsonResponse
    {
        $this->ajouterEtudiantsAuGroupe->execute([
            'groupe_id' => $id,
            'etudiant_ids' => $request->validated()['etudiant_ids'],
        ]);

        return response()->json([
            'message' => 'Étudiants ajoutés au groupe avec succès.',
        ], 200);
    }

    public function retirerEtudiant(int $id, int $etudiantId): JsonResponse
    {
        $this->retirerEtudiantDuGroupe->execute($id, $etudiantId);

        return response()->json([
            'message' => 'Étudiant retiré du groupe avec succès.',
        ], 200);
    }
}
