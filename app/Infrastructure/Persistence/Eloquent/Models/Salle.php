<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;

class Salle extends Model
{
    protected $table = 'salle';

    public $timestamps = false;

    protected $fillable = [
        'nom',
        'capacite',
    ];
}
