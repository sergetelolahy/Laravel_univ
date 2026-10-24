<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('semestre', function (Blueprint $table) {
            $table->id();
            $table->foreignId('filiere_id')->constrained('filiere')->restrictOnDelete();
            $table->unsignedSmallInteger('numero');
            $table->unique(['filiere_id', 'numero']);
        });

        DB::statement('ALTER TABLE semestre ADD CONSTRAINT chk_semestre_numero CHECK (numero BETWEEN 1 AND 10)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE semestre DROP CONSTRAINT chk_semestre_numero');
        Schema::dropIfExists('semestre');
    }
};
