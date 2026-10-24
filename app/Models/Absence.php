<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class Absence extends Model
{
    protected $table = 'absence';
    public $timestamps = false;

    protected $fillable = ['seance_id', 'etudiant_id', 'justifiee'];

    protected $casts = ['justifiee' => 'boolean'];

    public function seance(): BelongsTo
    {
        return $this->belongsTo(Seance::class, 'seance_id');
    }

    public function etudiant(): BelongsTo
    {
        return $this->belongsTo(Etudiant::class, 'etudiant_id');
    }
}
