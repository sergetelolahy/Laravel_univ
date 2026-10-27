<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Domain\Scolarite\Entities\Inscription;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Inscription */
final class InscriptionResource extends JsonResource
{
    public function toArray($request): array
    {
        /** @var Inscription $inscription */
        $inscription = $this->resource;

        return [
            'id' => $inscription->id,
            'etudiant_id' => $inscription->etudiantId,
            'cours_id' => $inscription->coursId,
            'annee_id' => $inscription->anneeId,
            'date_inscription' => $inscription->dateInscription,
            'etudiant' => $inscription->resumeEtudiant(),
            'cours' => $inscription->resumeCours(),
            'annee_universitaire' => $inscription->resumeAnnee(),
        ];
    }
}
