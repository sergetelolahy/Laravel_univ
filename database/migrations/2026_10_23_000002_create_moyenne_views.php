<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Vue des moyennes pondérées par étudiant et par cours.
       
        DB::statement(<<<'SQL'
            CREATE OR REPLACE VIEW v_moyenne_semestre AS
            SELECT
                mc.etudiant_id,
                mc.semestre_id,
                s.numero AS semestre,
                mc.annee_id,
                ROUND(
                    SUM(mc.moyenne * c.coefficient)
                    / NULLIF(SUM(c.coefficient), 0),
                    2
                ) AS moyenne
            FROM v_moyenne_cours AS mc
            INNER JOIN cours AS c
                ON c.id = mc.cours_id
            INNER JOIN semestre AS s
                ON s.id = mc.semestre_id
            GROUP BY
                mc.etudiant_id,
                mc.semestre_id,
                s.numero,
                mc.annee_id
        SQL);
    }

    /**
     * Supprimer les vues lors du rollback.
     */
    public function down(): void
    {
        DB::statement('DROP VIEW IF EXISTS v_moyenne_semestre');

        DB::statement('DROP VIEW IF EXISTS v_moyenne_cours');
    }
};


