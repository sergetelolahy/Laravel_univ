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
        Schema::create('annee_universitaire', function (Blueprint $table) {
            $table->id();
            $table->string('libelle', 9)->unique();
            $table->date('date_debut');
            $table->date('date_fin');
            $table->boolean('courante')->default(false);
        });

        DB::statement('ALTER TABLE annee_universitaire ADD CONSTRAINT chk_annee_universitaire_date CHECK (date_fin > date_debut)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE annee_universitaire DROP CONSTRAINT chk_annee_universitaire_date');
        Schema::dropIfExists('annee_universitaire');
    }
};
