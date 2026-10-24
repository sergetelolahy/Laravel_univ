<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

final class CoursResource extends JsonResource
{
    public function toArray($request): array
    {
        if (is_array($this->resource)) {
            $resource = $this->resource;
            $semestre = $resource['semestre'] ?? null;
            $filiere = $semestre !== null ? ($semestre['filiere'] ?? null) : null;

            return [
                'id' => $resource['id'],
                'code' => $resource['code'],
                'intitule' => $resource['intitule'],
                'coefficient' => (float) $resource['coefficient'],
                'credits' => $resource['credits'],
                'semestre_id' => $resource['semestre_id'] ?? ($semestre['id'] ?? null),
                'semestre' => $semestre !== null ? [
                    'id' => $semestre['id'],
                    'numero' => $semestre['numero'],
                    'libelle' => 'Semestre ' . $semestre['numero'],
                    'filiere' => $filiere !== null ? [
                        'id' => $filiere['id'],
                        'code' => $filiere['code'],
                        'nom' => $filiere['nom'],
                    ] : null,
                ] : null,
            ];
        }

        $semestre = $this->resource->semestre;
        $filiere = $semestre !== null ? $semestre->filiere : null;

        return [
            'id' => $this->resource->id,
            'code' => $this->resource->code,
            'intitule' => $this->resource->intitule,
            'coefficient' => (float) $this->resource->coefficient,
            'credits' => $this->resource->credits,
            'semestre_id' => $this->resource->semestreId,
            'semestre' => $semestre !== null ? [
                'id' => $semestre->id,
                'numero' => $semestre->numero,
                'libelle' => 'Semestre ' . $semestre->numero,
                'filiere' => $filiere !== null ? [
                    'id' => $filiere->id,
                    'code' => $filiere->code,
                    'nom' => $filiere->nom,
                ] : null,
            ] : null,
        ];
    }
}
