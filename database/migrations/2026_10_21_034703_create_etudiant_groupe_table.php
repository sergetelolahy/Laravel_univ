<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('etudiant_groupe', function (Blueprint $table) {
            $table->foreignId('etudiant_id')->constrained('etudiant')->cascadeOnDelete();
            $table->foreignId('groupe_id')->constrained('groupe')->cascadeOnDelete();
            $table->primary(['etudiant_id', 'groupe_id']);
            $table->index('groupe_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('etudiant_groupe');
    }
};
