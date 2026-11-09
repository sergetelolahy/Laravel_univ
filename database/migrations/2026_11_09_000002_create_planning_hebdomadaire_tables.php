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
        Schema::create('disponibilite_enseignant', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('enseignant_id')->constrained('enseignant')->cascadeOnDelete();
            $table->foreignId('annee_id')->constrained('annee_universitaire')->restrictOnDelete();
            $table->date('semaine_debut');
            $table->dateTime('debut');
            $table->dateTime('fin');
            $table->string('note', 255)->nullable();
            $table->timestamps();
            $table->index(['annee_id', 'semaine_debut']);
            $table->index(['enseignant_id', 'debut']);
        });

        Schema::create('edt_hebdomadaire', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('filiere_id')->constrained('filiere')->restrictOnDelete();
            $table->foreignId('annee_id')->constrained('annee_universitaire')->restrictOnDelete();
            $table->date('semaine_debut');
            $table->string('statut', 20)->default('PREPARATION');
            $table->foreignId('prepare_par')->constrained('utilisateur')->restrictOnDelete();
            $table->foreignId('valide_par')->nullable()->constrained('utilisateur')->nullOnDelete();
            $table->foreignId('publie_par')->nullable()->constrained('utilisateur')->nullOnDelete();
            $table->timestamp('valide_at')->nullable();
            $table->timestamp('publie_at')->nullable();
            $table->timestamps();
            $table->unique(['filiere_id', 'annee_id', 'semaine_debut'], 'edt_hebdo_filiere_annee_semaine_unique');
            $table->index(['semaine_debut', 'statut']);
        });

        Schema::create('edt_hebdomadaire_seance', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('edt_id')->constrained('edt_hebdomadaire')->cascadeOnDelete();
            $table->foreignId('cours_id')->constrained('cours')->restrictOnDelete();
            $table->foreignId('groupe_id')->constrained('groupe')->restrictOnDelete();
            $table->foreignId('enseignant_id')->constrained('enseignant')->restrictOnDelete();
            $table->foreignId('salle_id')->constrained('salle')->restrictOnDelete();
            $table->dateTime('debut');
            $table->dateTime('fin');
            $table->timestamps();
            $table->index(['edt_id', 'debut']);
            $table->index(['enseignant_id', 'debut']);
            $table->index(['salle_id', 'debut']);
            $table->index(['groupe_id', 'debut']);
        });

        Schema::table('seance', function (Blueprint $table): void {
            $table->foreignId('edt_seance_id')
                ->nullable()
                ->unique()
                ->constrained('edt_hebdomadaire_seance')
                ->nullOnDelete();
        });

        DB::statement(
            "ALTER TABLE edt_hebdomadaire ADD CONSTRAINT chk_edt_hebdomadaire_statut CHECK (statut IN ('PREPARATION','A_VALIDER','VALIDEE','PUBLIEE'))"
        );
        DB::statement(
            'ALTER TABLE edt_hebdomadaire_seance ADD CONSTRAINT chk_edt_seance_fin_apres_debut CHECK (fin > debut)'
        );
        DB::statement(
            'ALTER TABLE edt_hebdomadaire_seance ADD CONSTRAINT chk_edt_seance_meme_journee CHECK (DATE(debut) = DATE(fin))'
        );
        DB::statement(
            'ALTER TABLE disponibilite_enseignant ADD CONSTRAINT chk_disponibilite_fin_apres_debut CHECK (fin > debut)'
        );
        DB::statement(
            'ALTER TABLE disponibilite_enseignant ADD CONSTRAINT chk_disponibilite_meme_journee CHECK (DATE(debut) = DATE(fin))'
        );
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE disponibilite_enseignant DROP CONSTRAINT chk_disponibilite_meme_journee');
        DB::statement('ALTER TABLE disponibilite_enseignant DROP CONSTRAINT chk_disponibilite_fin_apres_debut');
        DB::statement('ALTER TABLE edt_hebdomadaire_seance DROP CONSTRAINT chk_edt_seance_meme_journee');
        DB::statement('ALTER TABLE edt_hebdomadaire_seance DROP CONSTRAINT chk_edt_seance_fin_apres_debut');
        DB::statement('ALTER TABLE edt_hebdomadaire DROP CONSTRAINT chk_edt_hebdomadaire_statut');

        Schema::table('seance', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('edt_seance_id');
        });
        Schema::dropIfExists('edt_hebdomadaire_seance');
        Schema::dropIfExists('edt_hebdomadaire');
        Schema::dropIfExists('disponibilite_enseignant');
    }
};
