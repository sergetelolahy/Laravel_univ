<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

final class SemestreResource extends JsonResource
{
    public function toArray($request): array
    {
        if (is_array($this->resource)) {
            $resource = $this->resource;
            $filiere = $resource['filiere'] ?? null;

            return [
                'id' => $resource['id'],
                'filiere_id' => $resource['filiere_id'] ?? ($filiere['id'] ?? null),
                'numero' => $resource['numero'],
                'libelle' => 'Semestre ' . $resource['numero'],
                'filiere' => $filiere !== null ? [
                    'id' => $filiere['id'],
                    'code' => $filiere['code'],
                    'nom' => $filiere['nom'],
                ] : null,
            ];
        }

        $filiere = $this->resource->filiere;

        return [
            'id' => $this->resource->id,
            'filiere_id' => $this->resource->filiereId,
            'numero' => $this->resource->numero,
            'libelle' => 'Semestre ' . $this->resource->numero,
            'filiere' => $filiere !== null ? [
                'id' => $filiere->id,
                'code' => $filiere->code,
                'nom' => $filiere->nom,
            ] : null,
        ];
    }
}
