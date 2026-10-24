<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Models;

use App\Infrastructure\Persistence\Eloquent\Models\Semestre;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Cours extends Model
{
    protected $table = 'cours';

    public $timestamps = false;

    protected $fillable = [
        'code',
        'intitule',
        'semestre_id',
        'coefficient',
        'credits',
    ];

    protected $casts = [
        'coefficient' => 'decimal:2',
    ];

    public function semestre(): BelongsTo
    {
        return $this->belongsTo(Semestre::class, 'semestre_id');
    }

    public function enseignants(): BelongsToMany
    {
        return $this->belongsToMany(Enseignant::class, 'enseignant_cours', 'cours_id', 'enseignant_id')
            ->withPivot('annee_id');
    }
}
