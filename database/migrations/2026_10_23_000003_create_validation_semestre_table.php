<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('validation_semestre', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('etudiant_id')->constrained('etudiant')->restrictOnDelete();
            $table->foreignId('semestre_id')->constrained('semestre')->restrictOnDelete();
            $table->foreignId('annee_id')->constrained('annee_universitaire')->restrictOnDelete();
            $table->decimal('moyenne', 4, 2);
            $table->boolean('valide')->default(false);
            $table->timestamp('valide_le')->nullable();
            $table->boolean('verrouille')->default(false);
            $table->timestamps();
            $table->unique(['etudiant_id', 'semestre_id', 'annee_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('validation_semestre');
    }
};
