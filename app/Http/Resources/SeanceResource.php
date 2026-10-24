<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Domain\Planning\Entities\Seance;
use Illuminate\Http\Resources\Json\JsonResource;

final class SeanceResource extends JsonResource
{
    /** @return array<string,mixed> */
    public function toArray($request): array
    {
        /** @var Seance $s */
        $s = $this->resource;

        return [
            'id' => $s->id,
            'debut' => $s->debut->format('Y-m-d H:i:s'),
            'fin' => $s->fin->format('Y-m-d H:i:s'),
            'duree_minutes' => intdiv($s->fin->getTimestamp() - $s->debut->getTimestamp(), 60),
            'cours' => $s->cours,
            'groupe' => $s->groupe,
            'enseignant' => $s->enseignant,
            'salle' => $s->salle,
        ];
    }
}
