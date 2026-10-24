<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class Note extends Model
{
    protected $table = 'note';
    public $timestamps = false;

    protected $fillable = [
        'evaluation_id',
        'etudiant_id',
        'valeur',
        'saisie_par',
        'date_saisie',
    ];

    protected $casts = [
        'valeur' => 'decimal:2',
        'date_saisie' => 'datetime',
    ];

    public function evaluation(): BelongsTo
    {
        return $this->belongsTo(Evaluation::class, 'evaluation_id');
    }

    public function etudiant(): BelongsTo
    {
        return $this->belongsTo(Etudiant::class, 'etudiant_id');
    }

    public function enseignantSaisie(): BelongsTo
    {
        return $this->belongsTo(Enseignant::class, 'saisie_par');
    }
}
