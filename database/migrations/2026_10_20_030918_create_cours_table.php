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
        Schema::create('cours', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->string('intitule', 150);
            $table->foreignId('semestre_id')->constrained('semestre')->restrictOnDelete();
            $table->decimal('coefficient', 4, 2);
            $table->unsignedSmallInteger('credits');
        });

        DB::statement('ALTER TABLE cours ADD CONSTRAINT chk_cours_coefficient CHECK (coefficient > 0)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE cours DROP CONSTRAINT chk_cours_coefficient');
        Schema::dropIfExists('cours');
    }
};
