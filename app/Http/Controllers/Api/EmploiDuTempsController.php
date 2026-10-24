<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Application\Planning\EmploiDuTempsEnseignant\EmploiDuTempsEnseignant;
use App\Application\Planning\EmploiDuTempsEtudiant\EmploiDuTempsEtudiant;
use App\Domain\Planning\Entities\Seance;
use App\Http\Controllers\Controller;
use App\Http\Requests\FiltreSeancesRequest;
use App\Http\Resources\SeanceResource;
use Illuminate\Http\JsonResponse;

final class EmploiDuTempsController extends Controller
{
    public function enseignant(FiltreSeancesRequest $request, EmploiDuTempsEnseignant $cas): JsonResponse
    {
        $r = $cas->execute((int) $request->user()->id, $request->input('du'), $request->input('au'));

        return response()->json(['data' => [
            'du' => $r['du'],
            'au' => $r['au'],
            'jours' => self::parJour($r['seances']),
        ]]);
    }

    public function etudiant(FiltreSeancesRequest $request, EmploiDuTempsEtudiant $cas): JsonResponse
    {
        $r = $cas->execute((int) $request->user()->id, $request->input('du'), $request->input('au'));

        return response()->json(['data' => [
            'du' => $r['du'],
            'au' => $r['au'],
            'jours' => self::parJour($r['seances']),
        ]]);
    }

    /**
     * Regroupe les séances par jour : [{ date, seances: [...] }].
     *
     * @param Seance[] $seances
     * @return array<int,array{date:string,seances:array<int,array<string,mixed>>}>
     */
    public static function parJour(array $seances): array
    {
        $jours = [];
        foreach ($seances as $seance) {
            $jours[$seance->debut->format('Y-m-d')][] = (new SeanceResource($seance))->resolve();
        }
        ksort($jours);

        $liste = [];
        foreach ($jours as $date => $items) {
            $liste[] = ['date' => $date, 'seances' => $items];
        }

        return $liste;
    }
}
