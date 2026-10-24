<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

final class FiliereResource extends JsonResource
{
    public function toArray($request): array
    {
        if (is_array($this->resource)) {
            return $this->resource;
        }

        return [
            'id' => $this->resource->id,
            'code' => $this->resource->code,
            'nom' => $this->resource->nom,
        ];
    }
}
