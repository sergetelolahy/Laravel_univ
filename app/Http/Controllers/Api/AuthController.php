<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Application\Identity\Command\Authentifier\Authentifier;
use App\Http\Requests\LoginRequest;
use App\Http\Resources\UtilisateurResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

final class AuthController extends Controller
{
    public function __construct(
        private readonly Authentifier $authentifier,
    ) {
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $resultat = $this->authentifier->execute(
            (string) $request->input('email'),
            (string) $request->input('password'),
        );

        return response()->json([
            'message' => 'Connexion réussie.',
            'token' => $resultat['token'],
            'utilisateur' => UtilisateurResource::make($resultat['utilisateur']),
        ], 200);
    }

    public function logout(Request $request): JsonResponse
    {
        $utilisateur = $request->user('sanctum');

        if ($utilisateur !== null) {
            $utilisateur->currentAccessToken()?->delete();
        }

        return response()->json([
            'message' => 'Déconnexion réussie.',
        ], 200);
    }

    public function me(Request $request): JsonResponse
    {
        $utilisateur = $request->user('sanctum');

        if ($utilisateur === null) {
            return response()->json([
                'message' => 'Utilisateur introuvable.',
            ], 404);
        }

        return response()->json([
            'message' => 'Profil récupéré avec succès.',
            'utilisateur' => UtilisateurResource::make($utilisateur),
        ], 200);
    }
}
