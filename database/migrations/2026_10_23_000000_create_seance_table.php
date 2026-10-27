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
        Schema::create('seance', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cours_id')->constrained('cours')->restrictOnDelete();
            $table->foreignId('groupe_id')->constrained('groupe')->restrictOnDelete();
            $table->foreignId('enseignant_id')->constrained('enseignant')->restrictOnDelete();
            $table->foreignId('salle_id')->constrained('salle')->restrictOnDelete();
            $table->dateTime('debut');
            $table->dateTime('fin');
            // Pas de timestamps

            // Index pour détection de conflits et emplois du temps
            $table->index(['salle_id', 'debut']);
            $table->index(['enseignant_id', 'debut']);
            $table->index(['groupe_id', 'debut']);
            $table->index(['cours_id']);
        });

        // CHECK : fin strictement après debut
        DB::unprepared(
            'ALTER TABLE seance ADD CONSTRAINT chk_seance_fin_apres_debut CHECK (fin > debut)'
        );

        // CHECK : séance sur une seule journée
        DB::unprepared(
            'ALTER TABLE seance ADD CONSTRAINT chk_seance_meme_journee CHECK (DATE(debut) = DATE(fin))'
        );

        // Corps commun des deux triggers
        $body = <<<'SQL'
BEGIN
    -- 5001 : chevauchement de salle
    IF EXISTS (
        SELECT 1 FROM seance
        WHERE salle_id = NEW.salle_id
          AND id       <> NEW.id
          AND debut    < NEW.fin
          AND fin      > NEW.debut
    ) THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Conflit de salle : la salle est déjà occupée sur ce créneau.',
                MYSQL_ERRNO  = 5001;
    END IF;

    -- 5002 : chevauchement enseignant
    IF EXISTS (
        SELECT 1 FROM seance
        WHERE enseignant_id = NEW.enseignant_id
          AND id            <> NEW.id
          AND debut         < NEW.fin
          AND fin           > NEW.debut
    ) THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Conflit d''enseignant : l''enseignant a déjà une séance sur ce créneau.',
                MYSQL_ERRNO  = 5002;
    END IF;

    -- 5003 : chevauchement groupe
    IF EXISTS (
        SELECT 1 FROM seance
        WHERE groupe_id = NEW.groupe_id
          AND id        <> NEW.id
          AND debut     < NEW.fin
          AND fin       > NEW.debut
    ) THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Conflit de groupe : le groupe a déjà une séance sur ce créneau.',
                MYSQL_ERRNO  = 5003;
    END IF;

    -- 5004 : taille du groupe > capacité de la salle
    IF (SELECT COUNT(*) FROM etudiant_groupe WHERE groupe_id = NEW.groupe_id)
       > (SELECT capacite FROM salle WHERE id = NEW.salle_id)
    THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Capacité insuffisante : le groupe dépasse la capacité de la salle.',
                MYSQL_ERRNO  = 5004;
    END IF;
END
SQL;

        // BEFORE INSERT
        DB::unprepared("CREATE TRIGGER trg_seance_bi
            BEFORE INSERT ON seance FOR EACH ROW
            {$body}");

        // BEFORE UPDATE (même corps)
        DB::unprepared("CREATE TRIGGER trg_seance_bu
            BEFORE UPDATE ON seance FOR EACH ROW
            {$body}");
    }

    public function down(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS trg_seance_bu');
        DB::unprepared('DROP TRIGGER IF EXISTS trg_seance_bi');
        Schema::dropIfExists('seance');
    }
};
