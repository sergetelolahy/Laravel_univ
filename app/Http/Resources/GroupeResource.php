<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

final class GroupeResource extends JsonResource
{
    public function toArray($request): array
    {
        if (is_array($this->resource)) {
            return $this->resource;
        }

        return [
            'id' => $this->resource->id,
            'nom' => $this->resource->nom,
            'filiere_id' => $this->resource->filiereId,
            'annee_id' => $this->resource->anneeId,
            'effectif_max' => $this->resource->effectifMax,
            'effectif_actuel' => $this->resource->effectifActuel,
        ];
    }
}
