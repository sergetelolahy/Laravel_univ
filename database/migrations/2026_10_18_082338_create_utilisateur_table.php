<?php

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
        Schema::create('utilisateur', function (Blueprint $table) {
            $table->id();
            $table->string('email', 150)->unique();
            $table->string('mot_de_passe_hash', 255);
            $table->string('role', 20);
            $table->boolean('actif')->default(true);
            $table->timestamp('derniere_connexion')->nullable();
        });

        DB::statement('ALTER TABLE utilisateur ENGINE=InnoDB');
        DB::statement('ALTER TABLE utilisateur CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
        DB::statement("ALTER TABLE utilisateur ADD CONSTRAINT chk_utilisateur_role CHECK (role IN ('ADMIN','SCOLARITE','ENSEIGNANT','ETUDIANT'))");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE utilisateur DROP CONSTRAINT chk_utilisateur_role');
        Schema::dropIfExists('utilisateur');
    }
};
