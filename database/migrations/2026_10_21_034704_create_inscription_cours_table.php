<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inscription_cours', function (Blueprint $table) {
            $table->id();
            $table->foreignId('etudiant_id')->constrained('etudiant')->restrictOnDelete();
            $table->foreignId('cours_id')->constrained('cours')->restrictOnDelete();
            $table->foreignId('annee_id')->constrained('annee_universitaire')->restrictOnDelete();
            $table->timestamp('date_inscription')->useCurrent();
            $table->unique(['etudiant_id', 'cours_id', 'annee_id']);
            $table->index(['cours_id', 'annee_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inscription_cours');
    }
};
