<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

final class AnneeUniversitaireResource extends JsonResource
{
    public function toArray($request): array
    {
        if (is_array($this->resource)) {
            return $this->resource;
        }

        return [
            'id' => $this->resource->id,
            'libelle' => $this->resource->libelle,
            'date_debut' => $this->resource->dateDebut->format('Y-m-d'),
            'date_fin' => $this->resource->dateFin->format('Y-m-d'),
            'courante' => (bool) $this->resource->courante,
        ];
    }
}
