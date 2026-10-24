<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Filiere extends Model
{
    protected $table = 'filiere';

    public $timestamps = false;

    protected $fillable = [
        'code',
        'nom',
    ];

    public function semestres(): HasMany
    {
        return $this->hasMany(Semestre::class, 'filiere_id');
    }

    public function etudiants(): HasMany
    {
        return $this->hasMany(Etudiant::class, 'filiere_id');
    }
}
