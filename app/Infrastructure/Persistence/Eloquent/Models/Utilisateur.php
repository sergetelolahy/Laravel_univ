<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Models;

use App\Domain\Identity\Enums\Role;
use App\Infrastructure\Persistence\Eloquent\Models\Etudiant;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class Utilisateur extends Authenticatable
{
    use HasApiTokens;

    protected $table = 'utilisateur';

    public $timestamps = false;

    protected $guarded = [];

    protected $hidden = [
        'mot_de_passe_hash',
    ];

    protected $casts = [
        'role' => Role::class,
        'actif' => 'boolean',
        'derniere_connexion' => 'datetime',
    ];

    public function getAuthPassword(): string
    {
        return $this->mot_de_passe_hash;
    }

    public function etudiant(): HasOne
    {
        return $this->hasOne(Etudiant::class, 'utilisateur_id', 'id');
    }

    public function enseignant(): HasOne
    {
        return $this->hasOne(Enseignant::class, 'utilisateur_id', 'id');
    }
}
