<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

final class EtudiantResource extends JsonResource
{
    public function toArray($request): array
    {
        if (is_array($this->resource)) {
            return $this->resource;
        }

        return [
            'id' => $this->resource->id,
            'utilisateur_id' => $this->resource->utilisateurId,
            'matricule' => $this->resource->matricule,
            'nom' => $this->resource->nom,
            'prenom' => $this->resource->prenom,
            'filiere_id' => $this->resource->filiereId,
            'filiere' => $this->resource->resumeFiliere(),
        ];
    }
}
