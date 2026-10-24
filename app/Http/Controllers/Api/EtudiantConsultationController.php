<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EtudiantConsultationController extends Controller
{
    /**
     * Trouve le dossier étudiant lié au compte connecté.
     */
    private function etudiantConnecte(Request $request): object
    {
        $utilisateur = $request->user();

        abort_unless(
            $utilisateur,
            401,
            'Utilisateur non authentifié.'
        );

        $etudiant = DB::table('etudiant')
            ->where('utilisateur_id', $utilisateur->id)
            ->first();

        abort_unless(
            $etudiant,
            403,
            'Aucun dossier étudiant n’est lié à ce compte.'
        );

        return $etudiant;
    }

    /**
     * GET : consulter les notes de l'étudiant connecté.
     */
    public function notes(Request $request)
    {
        $etudiant = $this->etudiantConnecte($request);

        // Récupérer le compte utilisateur associé à l'étudiant.
        $utilisateur = DB::table('utilisateur')
            ->where('id', $etudiant->utilisateur_id)
            ->first();

        // Récupérer les notes, les cours et les évaluations.
        $notes = DB::table('note as n')
            ->join('evaluation as ev', 'ev.id', '=', 'n.evaluation_id')
            ->join('cours as c', 'c.id', '=', 'ev.cours_id')
            ->where('n.etudiant_id', $etudiant->id)
            ->select([
                'c.id as cours_id',
                'c.code as code_cours',
                'c.intitule as intitule_cours',
                'c.coefficient',
                'c.credits',
                'ev.id as evaluation_id',
                'ev.type as type_evaluation',
                'ev.ponderation',
                'ev.date_eval',
                'n.valeur as note',
            ])
            ->orderBy('c.intitule')
            ->orderBy('ev.id')
            ->get();

        // Regrouper les évaluations par cours.
        $cours = $notes
            ->groupBy('cours_id')
            ->map(function ($evaluations) {
                $premiere = $evaluations->first();

                return [
                    'id' => $premiere->cours_id,
                    'code' => $premiere->code_cours,
                    'intitule' => $premiere->intitule_cours,
                    'coefficient' => $premiere->coefficient,
                    'credits' => $premiere->credits,
                    'evaluations' => $evaluations->map(function ($ligne) {
                        return [
                            'id' => $ligne->evaluation_id,
                            'type' => $ligne->type_evaluation,
                            'ponderation' => $ligne->ponderation,
                            'date_eval' => $ligne->date_eval,
                            'note' => $ligne->note,
                        ];
                    })->values(),
                ];
            })
            ->values();

        return response()->json([
            'message' => 'Notes récupérées avec succès.',

            'etudiant' => [
                'id' => $etudiant->id,
                'nom' => $etudiant->nom ?? null,
                'prenom' => $etudiant->prenom ?? null,
                'email' => $utilisateur->email ?? null,
                'matricule' => $etudiant->matricule ?? null,
            ],

            'statistiques' => [
                'nombre_notes' => $notes->count(),
                'nombre_cours' => $cours->count(),
            ],

            'data' => $cours,
        ]);
    }

        /**
         * GET : consulter les moyennes par cours et par semestre.
         */
        public function moyennes(Request $request)
    {
        $etudiant = $this->etudiantConnecte($request);

        // Informations du compte utilisateur.
        $utilisateur = DB::table('utilisateur')
            ->where('id', $etudiant->utilisateur_id)
            ->first();

        // Moyennes par cours.
        $parCours = DB::table('v_moyenne_cours as mc')
            ->join('cours as c', 'c.id', '=', 'mc.cours_id')
            ->where('mc.etudiant_id', $etudiant->id)
            ->select([
                'mc.cours_id',
                'c.code as code_cours',
                'c.intitule as cours',
                'c.coefficient',
                'c.credits',
                'mc.semestre_id',
                'mc.annee_id',
                'mc.moyenne',
            ])
            ->orderBy('mc.annee_id')
            ->orderBy('mc.semestre_id')
            ->orderBy('c.intitule')
            ->get()
            ->map(function ($cours) {
                $cours->moyenne = (float) $cours->moyenne;
                $cours->valide = $cours->moyenne >= 10;
                return $cours;
            });

        // Moyennes par semestre.
        $parSemestre = DB::table('v_moyenne_semestre as ms')
            ->where('ms.etudiant_id', $etudiant->id)
            ->select([
                'ms.semestre_id',
                'ms.semestre',
                'ms.annee_id',
                'ms.moyenne',
            ])
            ->orderBy('ms.annee_id')
            ->orderBy('ms.semestre')
            ->get()
            ->map(function ($semestre) {
                $semestre->moyenne = (float) $semestre->moyenne;
                $semestre->valide = $semestre->moyenne >= 10;
                return $semestre;
            });

        return response()->json([
            'message' => 'Moyennes récupérées avec succès.',

            'etudiant' => [
                'id' => $etudiant->id,
                'nom' => $etudiant->nom ?? null,
                'prenom' => $etudiant->prenom ?? null,
                'email' => $utilisateur->email ?? null,
                'matricule' => $etudiant->matricule ?? null,
            ],

            'statistiques' => [
                'nombre_cours' => $parCours->count(),
                'nombre_semestres' => $parSemestre->count(),
                'nombre_cours_valides' => $parCours
                    ->where('valide', true)->count(),
                'nombre_cours_non_valides' => $parCours
                    ->where('valide', false)->count(),
            ],

            'moyennes_cours' => $parCours->values(),
            'moyennes_semestres' => $parSemestre->values(),
        ]);
    }

    /**
     * GET : consulter l'emploi du temps de l'étudiant connecté.
     */
    public function emploiDuTemps(Request $request)
    {
        $etudiant = $this->etudiantConnecte($request);

        $utilisateur = DB::table('utilisateur')
            ->where('id', $etudiant->utilisateur_id)
            ->first();

        // Récupérer les groupes de l'étudiant.
        $groupes = DB::table('etudiant_groupe as eg')
            ->join('groupe as g', 'g.id', '=', 'eg.groupe_id')
            ->where('eg.etudiant_id', $etudiant->id)
            ->select('g.*')
            ->get();

        $groupeIds = $groupes->pluck('id');

        // Récupérer les séances uniquement si des groupes existent.
        $seances = collect();

        if ($groupeIds->isNotEmpty()) {
            $seances = DB::table('seance as s')
                ->join('cours as c', 'c.id', '=', 's.cours_id')
                ->leftJoin('groupe as g', 'g.id', '=', 's.groupe_id')
                ->leftJoin('salle as sa', 'sa.id', '=', 's.salle_id')
                ->whereIn('s.groupe_id', $groupeIds)
                ->select([
                    's.id as seance_id',
                    's.date_seance',
                    's.heure_debut',
                    's.heure_fin',
                    'c.id as cours_id',
                    'c.code as code_cours',
                    'c.intitule as cours',
                    'g.id as groupe_id',
                    'g.nom as groupe',
                    'sa.id as salle_id',
                    'sa.nom as salle',
                ])
                ->orderBy('s.date_seance')
                ->orderBy('s.heure_debut')
                ->get();
        }

        return response()->json([
            'message' => $groupeIds->isEmpty()
                ? 'Aucun groupe n’est associé à cet étudiant.'
                : 'Emploi du temps récupéré avec succès.',

            'etudiant' => [
                'id' => $etudiant->id,
                'nom' => $etudiant->nom ?? null,
                'prenom' => $etudiant->prenom ?? null,
                'email' => $utilisateur->email ?? null,
                'matricule' => $etudiant->matricule ?? null,
            ],

            'statistiques' => [
                'nombre_groupes' => $groupes->count(),
                'nombre_seances' => $seances->count(),
            ],

            'groupes' => $groupes->values(),

            'emploi_du_temps' => $seances->values(),

            'data' => $seances->values(),
        ]);
    }
}