<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Evaluation extends Model
{
    protected $table = 'evaluation';
    public $timestamps = false;

    protected $fillable = [
        'cours_id',
        'annee_id',
        'type',
        'ponderation',
        'date_eval',
    ];

    protected $casts = [
        'ponderation' => 'decimal:2',
        'date_eval' => 'date',
    ];

    public function cours(): BelongsTo
    {
        return $this->belongsTo(Cours::class, 'cours_id');
    }

    public function notes(): HasMany
    {
        return $this->hasMany(Note::class, 'evaluation_id');
    }
}
