<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Application\Identity\Command\CreerUtilisateur\CreerUtilisateur;
use App\Application\Identity\Command\DesactiverUtilisateur\DesactiverUtilisateur;
use App\Application\Identity\Query\ListerUtilisateurs\ListerUtilisateurs;
use App\Domain\Identity\Enums\Role;
use App\Http\Requests\CreerUtilisateurRequest;
use App\Http\Requests\ListeUtilisateursRequest;
use App\Http\Resources\UtilisateurResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

final class UtilisateurController extends Controller
{
    public function __construct(
        private readonly CreerUtilisateur $creerUtilisateur,
        private readonly ListerUtilisateurs $listerUtilisateurs,
        private readonly DesactiverUtilisateur $desactiverUtilisateur,
    ) {
    }

    public function store(CreerUtilisateurRequest $request): JsonResponse
    {
        $donnees = $request->validated();
        $donnees['motDePasse'] = $donnees['password'];
        unset($donnees['password']);

        $roleConnecte = $request->user()->role;
        $resultat = $this->creerUtilisateur->execute(
            $request->user()->id,
            $roleConnecte,
            $donnees,
        );

        return response()->json([
            'message' => 'Utilisateur créé avec succès.',
            'utilisateur' => UtilisateurResource::make($resultat['utilisateur']),
            'profil' => $resultat['profil'],
        ], 201);
    }

    public function index(ListeUtilisateursRequest $request): JsonResponse
    {
        $role = $request->input('role');
        $queryRole = $role !== null && $role !== '' ? Role::from($role) : null;
        $page = (int) ($request->input('page', 1));
        $perPage = (int) ($request->input('per_page', 20));

        $utilisateurs = $this->listerUtilisateurs->execute($queryRole, $page, $perPage);

        return response()->json([
            'message' => 'Utilisateurs récupérés avec succès.',
            'data' => UtilisateurResource::collection($utilisateurs),
        ], 200);
    }

    public function desactiver(int $id, Request $request): JsonResponse
    {
        $this->desactiverUtilisateur->execute($id);

        return response()->json([
            'message' => 'Utilisateur désactivé avec succès.',
        ], 200);
    }
}
