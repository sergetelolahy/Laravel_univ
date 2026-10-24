<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Groupe extends Model
{
    protected $table = 'groupe';

    public $timestamps = false;

    protected $fillable = [
        'nom',
        'filiere_id',
        'annee_id',
        'effectif_max',
    ];

    protected $casts = [
        'effectif_max' => 'integer',
    ];

    public function filiere(): BelongsTo
    {
        return $this->belongsTo(Filiere::class, 'filiere_id');
    }

    public function anneeUniversitaire(): BelongsTo
    {
        return $this->belongsTo(AnneeUniversitaire::class, 'annee_id');
    }

    public function etudiants(): BelongsToMany
    {
        return $this->belongsToMany(Etudiant::class, 'etudiant_groupe', 'groupe_id', 'etudiant_id');
    }
}
