<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;

class AnneeUniversitaire extends Model
{
    protected $table = 'annee_universitaire';

    public $timestamps = false;

    protected $fillable = [
        'libelle',
        'date_debut',
        'date_fin',
        'courante',
    ];

    protected $casts = [
        'date_debut' => 'date',
        'date_fin' => 'date',
        'courante' => 'boolean',
    ];
}
