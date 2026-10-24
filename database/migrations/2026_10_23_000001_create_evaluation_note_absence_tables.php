<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evaluation', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('cours_id')->constrained('cours')->restrictOnDelete();
            $table->foreignId('annee_id')->constrained('annee_universitaire')->restrictOnDelete();
            $table->string('type', 30);
            $table->decimal('ponderation', 4, 2);
            $table->date('date_eval')->nullable();
            $table->index(['cours_id', 'annee_id']);
        });

        Schema::create('note', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('evaluation_id')->constrained('evaluation')->cascadeOnDelete();
            $table->foreignId('etudiant_id')->constrained('etudiant')->restrictOnDelete();
            $table->decimal('valeur', 4, 2);
            $table->foreignId('saisie_par')->nullable()->constrained('enseignant')->nullOnDelete();
            $table->timestamp('date_saisie')->useCurrent();
            $table->unique(['evaluation_id', 'etudiant_id']);
            $table->index('etudiant_id');
        });

        Schema::create('absence', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('seance_id')->constrained('seance')->cascadeOnDelete();
            $table->foreignId('etudiant_id')->constrained('etudiant')->restrictOnDelete();
            $table->boolean('justifiee')->default(false);
            $table->unique(['seance_id', 'etudiant_id']);
        });

        // MySQL 8.0.16+ applique les contraintes CHECK.
        DB::statement('ALTER TABLE note ADD CONSTRAINT note_valeur_entre_0_20 CHECK (valeur BETWEEN 0 AND 20)');
        DB::statement('ALTER TABLE evaluation ADD CONSTRAINT evaluation_ponderation_positive CHECK (ponderation > 0)');

        // RG8 : l'étudiant doit être inscrit au cours de l'évaluation.
        // RG9 : l'enseignant qui saisit doit être affecté au cours pour cette année.
        // RG10 : une note ne peut être créée/modifiée après verrouillage du semestre.
        if (DB::getDriverName() === 'mysql') {
            DB::unprepared(<<<'SQL'
CREATE TRIGGER trg_note_regles_bi BEFORE INSERT ON note
FOR EACH ROW
BEGIN
    DECLARE v_cours BIGINT;
    DECLARE v_annee BIGINT;
    DECLARE v_semestre BIGINT;
    DECLARE v_inscrit INT DEFAULT 0;
    DECLARE v_affecte INT DEFAULT 0;
    DECLARE v_verrouille INT DEFAULT 0;

    SELECT cours_id, annee_id INTO v_cours, v_annee
    FROM evaluation WHERE id = NEW.evaluation_id;

    SELECT semestre_id INTO v_semestre FROM cours WHERE id = v_cours;

    SELECT COUNT(*) INTO v_inscrit FROM inscription_cours
    WHERE etudiant_id = NEW.etudiant_id AND cours_id = v_cours AND annee_id = v_annee;
    IF v_inscrit = 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'RG8: etudiant non inscrit au cours';
    END IF;

    IF NEW.saisie_par IS NOT NULL THEN
        SELECT COUNT(*) INTO v_affecte FROM enseignant_cours
        WHERE enseignant_id = NEW.saisie_par AND cours_id = v_cours AND annee_id = v_annee;
        IF v_affecte = 0 THEN
            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'RG9: enseignant non affecte a ce cours';
        END IF;
    END IF;

    SELECT COUNT(*) INTO v_verrouille FROM validation_semestre
    WHERE etudiant_id = NEW.etudiant_id AND semestre_id = v_semestre
      AND annee_id = v_annee AND verrouille = 1;
    IF v_verrouille > 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'RG10: notes verrouillees';
    END IF;
END
SQL);

            DB::unprepared(<<<'SQL'
CREATE TRIGGER trg_note_regles_bu BEFORE UPDATE ON note
FOR EACH ROW
BEGIN
    DECLARE v_cours BIGINT;
    DECLARE v_annee BIGINT;
    DECLARE v_semestre BIGINT;
    DECLARE v_inscrit INT DEFAULT 0;
    DECLARE v_affecte INT DEFAULT 0;
    DECLARE v_verrouille INT DEFAULT 0;

    SELECT cours_id, annee_id INTO v_cours, v_annee
    FROM evaluation WHERE id = NEW.evaluation_id;

    SELECT semestre_id INTO v_semestre FROM cours WHERE id = v_cours;

    SELECT COUNT(*) INTO v_inscrit FROM inscription_cours
    WHERE etudiant_id = NEW.etudiant_id AND cours_id = v_cours AND annee_id = v_annee;
    IF v_inscrit = 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'RG8: etudiant non inscrit au cours';
    END IF;

    IF NEW.saisie_par IS NOT NULL THEN
        SELECT COUNT(*) INTO v_affecte FROM enseignant_cours
        WHERE enseignant_id = NEW.saisie_par AND cours_id = v_cours AND annee_id = v_annee;
        IF v_affecte = 0 THEN
            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'RG9: enseignant non affecte a ce cours';
        END IF;
    END IF;

    SELECT COUNT(*) INTO v_verrouille FROM validation_semestre
    WHERE etudiant_id = NEW.etudiant_id AND semestre_id = v_semestre
      AND annee_id = v_annee AND verrouille = 1;
    IF v_verrouille > 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'RG10: notes verrouillees';
    END IF;
END
SQL);
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::unprepared('DROP TRIGGER IF EXISTS trg_note_regles_bi');
            DB::unprepared('DROP TRIGGER IF EXISTS trg_note_regles_bu');
        }

        Schema::dropIfExists('absence');
        Schema::dropIfExists('note');
        Schema::dropIfExists('evaluation');
    }
};
