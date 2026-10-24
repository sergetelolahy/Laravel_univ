<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Application\Planning\OccupationSalle\OccupationSalle;
use App\Application\Planning\SallesDisponibles\SallesDisponibles;
use App\Http\Controllers\Controller;
use App\Http\Requests\FiltreSeancesRequest;
use App\Http\Requests\SallesDisponiblesRequest;
use Illuminate\Http\JsonResponse;

final class SalleDisponibiliteController extends Controller
{
    public function disponibles(SallesDisponiblesRequest $request, SallesDisponibles $cas): JsonResponse
    {
        $salles = $cas->execute(
            \DateTimeImmutable::createFromFormat('!Y-m-d H:i:s', (string) $request->input('debut')),
            \DateTimeImmutable::createFromFormat('!Y-m-d H:i:s', (string) $request->input('fin')),
            (int) $request->input('capacite_min', 0)
        );

        return response()->json(['data' => array_map(
            static fn ($s) => ['id' => $s->id, 'nom' => $s->nom, 'capacite' => $s->capacite],
            $salles
        )]);
    }

    public function occupation(int $id, FiltreSeancesRequest $request, OccupationSalle $cas): JsonResponse
    {
        $r = $cas->execute($id, $request->input('du'), $request->input('au'));

        return response()->json(['data' => [
            'salle' => $r['salle'],
            'du' => $r['du'],
            'au' => $r['au'],
            'jours' => EmploiDuTempsController::parJour($r['seances']),
        ]]);
    }
}
