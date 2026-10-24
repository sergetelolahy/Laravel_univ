<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Application\Planning\ListerSeances\ListerSeances;
use App\Application\Planning\ModifierSeance\ModifierSeance;
use App\Application\Planning\ObtenirSeance\ObtenirSeance;
use App\Application\Planning\PlanifierSeance\PlanifierSeance;
use App\Application\Planning\SupprimerSeance\SupprimerSeance;
use App\Http\Controllers\Controller;
use App\Http\Requests\FiltreSeancesRequest;
use App\Http\Requests\SeanceRequest;
use App\Http\Resources\SeanceResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\Response;

final class SeanceController extends Controller
{
    private const PAR_PAGE = 15;

    public function index(FiltreSeancesRequest $request, ListerSeances $cas): AnonymousResourceCollection
    {
        $page = max(1, (int) $request->query('page', 1));
        $resultat = $cas->execute($request->filtres(), $page, self::PAR_PAGE);

        $paginateur = new LengthAwarePaginator(
            $resultat->items,
            $resultat->total,
            self::PAR_PAGE,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return SeanceResource::collection($paginateur);
    }

    public function show(int $id, ObtenirSeance $cas): SeanceResource
    {
        return new SeanceResource($cas->execute($id));
    }

    public function store(SeanceRequest $request, PlanifierSeance $cas): JsonResponse
    {
        $seance = $cas->execute($request->donnees());

        return (new SeanceResource($seance))->response()->setStatusCode(201);
    }

    public function update(int $id, SeanceRequest $request, ModifierSeance $cas): SeanceResource
    {
        return new SeanceResource($cas->execute($id, $request->donnees()));
    }

    public function destroy(int $id, SupprimerSeance $cas): Response
    {
        $cas->execute($id);

        return response()->noContent();
    }
}
