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
        Schema::create('salle', function (Blueprint $table) {
            $table->id();
            $table->string('nom', 50)->unique();
            $table->unsignedInteger('capacite');
        });

        DB::statement('ALTER TABLE salle ADD CONSTRAINT chk_salle_capacite CHECK (capacite > 0)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE salle DROP CONSTRAINT chk_salle_capacite');
        Schema::dropIfExists('salle');
    }
};
