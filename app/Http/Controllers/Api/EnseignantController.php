<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Domain\Identity\Enums\Role;
use App\Infrastructure\Persistence\Eloquent\Models\Utilisateur;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

final class EnseignantController extends Controller
{
    public function index(): JsonResponse
    {
        $page = (int) request()->query('page', 1);
        $enseignants = Utilisateur::query()
            ->join('enseignant', 'enseignant.utilisateur_id', '=', 'utilisateur.id')
            ->where('utilisateur.role', Role::ENSEIGNANT->value)
            ->select([
                'utilisateur.id as id',
                'enseignant.nom as nom',
                'enseignant.prenom as prenom',
                'enseignant.specialite as specialite',
                'utilisateur.email as email',
            ])
            ->paginate(15, ['*'], 'page', $page);

        return response()->json([
            'data' => $enseignants->items(),
            'meta' => [
                'current_page' => $enseignants->currentPage(),
                'per_page' => $enseignants->perPage(),
                'total' => $enseignants->total(),
                'last_page' => $enseignants->lastPage(),
            ],
        ], 200);
    }
}
