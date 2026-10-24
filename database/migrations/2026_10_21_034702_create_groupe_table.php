<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('groupe', function (Blueprint $table) {
            $table->id();
            $table->string('nom', 50);
            $table->foreignId('filiere_id')->constrained('filiere')->restrictOnDelete();
            $table->foreignId('annee_id')->constrained('annee_universitaire')->restrictOnDelete();
            $table->unsignedSmallInteger('effectif_max');
            $table->unique(['nom', 'filiere_id', 'annee_id']);
        });

        DB::statement('ALTER TABLE groupe ADD CONSTRAINT chk_groupe_effectif_max CHECK (effectif_max > 0)');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE groupe DROP CONSTRAINT chk_groupe_effectif_max');
        Schema::dropIfExists('groupe');
    }
};
