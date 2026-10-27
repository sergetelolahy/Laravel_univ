<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

final class Seance extends Model
{
    protected $table = 'seance';
    public $timestamps = false;

    protected $fillable = [
        'cours_id',
        'groupe_id',
        'enseignant_id',
        'salle_id',
        'debut',
        'fin',
    ];

    protected $casts = [
        'debut' => 'datetime',
        'fin' => 'datetime',
    ];
}
