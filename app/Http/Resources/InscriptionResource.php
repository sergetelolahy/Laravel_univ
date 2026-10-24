<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

final class InscriptionResource extends JsonResource
{
    public function toArray($request): array
    {
        // if (is_array($this->resource)) {
        //     return $this->resource;
        // }

        return [
        'id' => $this->resource['id']
            ?? $this->resource->id
            ?? null,

        'etudiant_id' => $this->resource['etudiant_id']
            ?? $this->resource->etudiantId
            ?? null,

        'cours_id' => $this->resource['cours_id']
            ?? $this->resource->coursId
            ?? null,

        'annee_id' => $this->resource['annee_id']
            ?? $this->resource->anneeId
            ?? null,

        'date_inscription' => $this->resource['date_inscription']
            ?? $this->resource->dateInscription
            ?? null,

        'etudiant' => $this->resource['etudiant'] ?? null,

        'cours' => $this->resource['cours'] ?? null,

        'annee_universitaire' =>
            $this->resource['annee_universitaire'] ?? null,
    ];
    }
}
