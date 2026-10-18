<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Domain\Identity\Entities\Utilisateur;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Utilisateur */
final class UtilisateurResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        $profil = is_array($this->profil) ? $this->profil : [];

        if (isset($profil['date_naissance']) && $profil['date_naissance'] instanceof \DateTimeInterface) {
            $profil['date_naissance'] = $profil['date_naissance']->format('Y-m-d');
        }

        return [
            'id' => $this->id,
            'email' => $this->email,
            'role' => $this->role->value,
            'actif' => $this->actif,
            'derniere_connexion' => $this->derniereConnexion?->format('Y-m-d H:i:s'),
            'profil' => $profil,
        ];
    }
}
