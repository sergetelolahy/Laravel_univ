<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Domain\Identity\Enums\Role;
use App\Http\Controllers\Controller;
use DateTimeImmutable;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class PlanningHebdomadaireController extends Controller
{
    public function disponibilites(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'semaine_debut' => ['required', 'date_format:Y-m-d'],
        ]);
        $semaine = $validated['semaine_debut'];
        $this->validerLundi($semaine);
        $anneeId = DB::table('annee_universitaire')->where('courante', true)->value('id');

        $query = DB::table('disponibilite_enseignant as d')
            ->join('enseignant as e', 'e.id', '=', 'd.enseignant_id')
            ->join('utilisateur as u', 'u.id', '=', 'e.utilisateur_id')
            ->where('d.semaine_debut', $semaine)
            ->where('d.annee_id', $anneeId)
            ->select([
                'd.id',
                'd.enseignant_id',
                'e.nom',
                'e.prenom',
                'u.email',
                'd.semaine_debut',
                'd.debut',
                'd.fin',
                'd.note',
            ])
            ->orderBy('d.debut');

        if ($request->user()->role === Role::ENSEIGNANT) {
            $enseignantId = DB::table('enseignant')->where('utilisateur_id', $request->user()->id)->value('id');
            abort_if($enseignantId === null, 403, 'Aucun profil enseignant n’est associé à ce compte.');
            $query->where('d.enseignant_id', $enseignantId);
        }

        return response()->json([
            'data' => $query->get(),
            'semaine_debut' => $semaine,
            'annee_id' => $anneeId,
        ]);
    }

    public function enregistrerDisponibilites(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'semaine_debut' => ['required', 'date_format:Y-m-d'],
            'disponibilites' => ['required', 'array', 'min:1', 'max:20'],
            'disponibilites.*.debut' => ['required', 'date_format:Y-m-d H:i:s'],
            'disponibilites.*.fin' => ['required', 'date_format:Y-m-d H:i:s', 'after:disponibilites.*.debut'],
            'disponibilites.*.note' => ['nullable', 'string', 'max:255'],
        ]);
        $semaine = $validated['semaine_debut'];
        $this->validerLundi($semaine);
        $anneeId = DB::table('annee_universitaire')->where('courante', true)->value('id');
        abort_if($anneeId === null, 409, 'Aucune année universitaire courante n’est définie.');
        $enseignantId = DB::table('enseignant')->where('utilisateur_id', $request->user()->id)->value('id');
        abort_if($enseignantId === null, 403, 'Aucun profil enseignant n’est associé à ce compte.');

        $borneDebut = new DateTimeImmutable($semaine.' 00:00:00');
        $borneFin = $borneDebut->modify('+7 days');
        $creneaux = [];
        foreach ($validated['disponibilites'] as $disponibilite) {
            $debut = new DateTimeImmutable($disponibilite['debut']);
            $fin = new DateTimeImmutable($disponibilite['fin']);
            if ($debut < $borneDebut || $fin > $borneFin || $debut->format('Y-m-d') !== $fin->format('Y-m-d')) {
                throw ValidationException::withMessages([
                    'disponibilites' => 'Chaque disponibilité doit être comprise entre le lundi et le dimanche de la semaine choisie, sur une même journée.',
                ]);
            }
            if (($fin->getTimestamp() - $debut->getTimestamp()) > 4 * 60 * 60) {
                throw ValidationException::withMessages([
                    'disponibilites' => 'Une plage de disponibilité ne peut pas dépasser quatre heures.',
                ]);
            }
            $creneaux[] = [
                'debut' => $debut,
                'fin' => $fin,
                'note' => $disponibilite['note'] ?? null,
            ];
        }

        usort($creneaux, static fn (array $a, array $b): int => $a['debut'] <=> $b['debut']);
        for ($index = 1; $index < count($creneaux); $index++) {
            if ($creneaux[$index]['debut'] < $creneaux[$index - 1]['fin']) {
                throw ValidationException::withMessages([
                    'disponibilites' => 'Vos plages de disponibilité ne doivent pas se chevaucher.',
                ]);
            }
        }

        $dejaPlanifie = DB::table('edt_hebdomadaire_seance as es')
            ->join('edt_hebdomadaire as edt', 'edt.id', '=', 'es.edt_id')
            ->where('edt.annee_id', $anneeId)
            ->where('edt.semaine_debut', $semaine)
            ->where('es.enseignant_id', $enseignantId)
            ->where('edt.statut', '!=', 'PREPARATION')
            ->exists();
        abort_if($dejaPlanifie, 409, 'Ces disponibilités ne peuvent plus être modifiées : un emploi du temps a déjà été soumis à validation.');

        $rows = array_map(fn (array $creneau): array => [
            'enseignant_id' => $enseignantId,
            'annee_id' => $anneeId,
            'semaine_debut' => $semaine,
            'debut' => $creneau['debut']->format('Y-m-d H:i:s'),
            'fin' => $creneau['fin']->format('Y-m-d H:i:s'),
            'note' => $creneau['note'],
            'created_at' => now(),
            'updated_at' => now(),
        ], $creneaux);

        DB::transaction(function () use ($enseignantId, $anneeId, $semaine, $rows): void {
            DB::table('disponibilite_enseignant')
                ->where('enseignant_id', $enseignantId)
                ->where('annee_id', $anneeId)
                ->where('semaine_debut', $semaine)
                ->delete();
            DB::table('disponibilite_enseignant')->insert($rows);
        });

        return response()->json(['message' => 'Disponibilités hebdomadaires enregistrées.', 'data' => $rows], 201);
    }

    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'semaine_debut' => ['required', 'date_format:Y-m-d'],
            'filiere_id' => ['nullable', 'integer', 'exists:filiere,id'],
        ]);
        $this->validerLundi($validated['semaine_debut']);
        $query = DB::table('edt_hebdomadaire as edt')
            ->join('filiere as f', 'f.id', '=', 'edt.filiere_id')
            ->join('annee_universitaire as a', 'a.id', '=', 'edt.annee_id')
            ->leftJoin('utilisateur as prepareur', 'prepareur.id', '=', 'edt.prepare_par')
            ->leftJoin('utilisateur as validateur', 'validateur.id', '=', 'edt.valide_par')
            ->leftJoin('utilisateur as editeur', 'editeur.id', '=', 'edt.publie_par')
            ->where('edt.semaine_debut', $validated['semaine_debut'])
            ->select([
                'edt.id',
                'edt.filiere_id',
                'f.code as filiere_code',
                'f.nom as filiere_nom',
                'edt.annee_id',
                'a.libelle as annee',
                'edt.semaine_debut',
                'edt.statut',
                'edt.prepare_par',
                'prepareur.email as prepareur_email',
                'edt.valide_par',
                'validateur.email as validateur_email',
                'edt.valide_at',
                'edt.publie_par',
                'editeur.email as editeur_email',
                'edt.publie_at',
                'edt.created_at',
            ])
            ->orderBy('f.code');

        if (isset($validated['filiere_id'])) {
            $query->where('edt.filiere_id', $validated['filiere_id']);
        }

        return response()->json(['data' => $query->get()]);
    }

    public function creer(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'filiere_id' => ['required', 'integer', 'exists:filiere,id'],
            'semaine_debut' => ['required', 'date_format:Y-m-d'],
        ]);
        $this->validerLundi($validated['semaine_debut']);
        $anneeId = DB::table('annee_universitaire')->where('courante', true)->value('id');
        abort_if($anneeId === null, 409, 'Aucune année universitaire courante n’est définie.');

        $id = DB::table('edt_hebdomadaire')->where([
            'filiere_id' => $validated['filiere_id'],
            'annee_id' => $anneeId,
            'semaine_debut' => $validated['semaine_debut'],
        ])->value('id');

        if ($id === null) {
            $id = DB::table('edt_hebdomadaire')->insertGetId([
                'filiere_id' => $validated['filiere_id'],
                'annee_id' => $anneeId,
                'semaine_debut' => $validated['semaine_debut'],
                'statut' => 'PREPARATION',
                'prepare_par' => $request->user()->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return response()->json([
            'message' => 'Emploi du temps hebdomadaire ouvert.',
            'data' => $this->chargerPlanning((int) $id),
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        return response()->json(['data' => $this->chargerPlanning($id)]);
    }

    public function ajouterSeance(int $id, Request $request): JsonResponse
    {
        $validated = $request->validate([
            'cours_id' => ['required', 'integer', 'exists:cours,id'],
            'groupe_id' => ['required', 'integer', 'exists:groupe,id'],
            'enseignant_id' => ['required', 'integer', 'exists:enseignant,id'],
            'salle_id' => ['required', 'integer', 'exists:salle,id'],
            'debut' => ['required', 'date_format:Y-m-d H:i:s'],
            'fin' => ['required', 'date_format:Y-m-d H:i:s', 'after:debut'],
        ]);
        $planning = DB::table('edt_hebdomadaire')->where('id', $id)->first();
        abort_if($planning === null, 404, 'Emploi du temps introuvable.');
        abort_unless($planning->statut === 'PREPARATION', 409, 'Cet emploi du temps n’est plus modifiable.');

        $debut = new DateTimeImmutable($validated['debut']);
        $fin = new DateTimeImmutable($validated['fin']);
        $borneDebut = new DateTimeImmutable($planning->semaine_debut.' 00:00:00');
        $borneFin = $borneDebut->modify('+7 days');
        abort_if(
            $debut < $borneDebut || $fin > $borneFin || $debut->format('Y-m-d') !== $fin->format('Y-m-d'),
            422,
            'La séance doit avoir lieu pendant la semaine choisie et se terminer le même jour.'
        );
        abort_if(($fin->getTimestamp() - $debut->getTimestamp()) > 4 * 60 * 60, 422, 'Une séance ne peut pas dépasser quatre heures.');

        $filiereCours = DB::table('cours as c')
            ->join('semestre as s', 's.id', '=', 'c.semestre_id')
            ->where('c.id', $validated['cours_id'])
            ->value('s.filiere_id');
        abort_unless((int) $filiereCours === (int) $planning->filiere_id, 422, 'Le cours ne correspond pas au parcours de cet emploi du temps.');

        $groupe = DB::table('groupe')->where('id', $validated['groupe_id'])->first();
        abort_unless(
            $groupe !== null
                && (int) $groupe->filiere_id === (int) $planning->filiere_id
                && (int) $groupe->annee_id === (int) $planning->annee_id,
            422,
            'Le groupe ne correspond pas au parcours et à l’année de cet emploi du temps.'
        );

        $affecte = DB::table('enseignant_cours')->where([
            'enseignant_id' => $validated['enseignant_id'],
            'cours_id' => $validated['cours_id'],
            'annee_id' => $planning->annee_id,
        ])->exists();
        abort_unless($affecte, 422, 'Cet enseignant n’est pas affecté à ce cours pour cette année universitaire.');

        $disponible = DB::table('disponibilite_enseignant')
            ->where('enseignant_id', $validated['enseignant_id'])
            ->where('annee_id', $planning->annee_id)
            ->where('semaine_debut', $planning->semaine_debut)
            ->where('debut', '<=', $debut->format('Y-m-d H:i:s'))
            ->where('fin', '>=', $fin->format('Y-m-d H:i:s'))
            ->exists();
        abort_unless($disponible, 422, 'Le créneau ne se trouve pas dans les disponibilités déclarées par cet enseignant.');

        $capacite = DB::table('salle')->where('id', $validated['salle_id'])->value('capacite');
        abort_if($capacite === null, 422, 'La salle sélectionnée est introuvable.');
        $effectif = DB::table('etudiant_groupe')->where('groupe_id', $groupe->id)->count();
        abort_if($effectif > $capacite, 422, 'La capacité de la salle est inférieure à l’effectif du groupe.');

        $chevauchementPublie = DB::table('seance')
            ->where('debut', '<', $fin->format('Y-m-d H:i:s'))
            ->where('fin', '>', $debut->format('Y-m-d H:i:s'))
            ->where(function ($query) use ($validated): void {
                $query->where('enseignant_id', $validated['enseignant_id'])
                    ->orWhere('groupe_id', $validated['groupe_id'])
                    ->orWhere('salle_id', $validated['salle_id']);
            })
            ->exists();
        abort_if($chevauchementPublie, 409, 'Le créneau choisi entre en conflit avec un emploi du temps déjà publié.');

        $chevauchementPlanifie = DB::table('edt_hebdomadaire_seance')
            ->where('debut', '<', $fin->format('Y-m-d H:i:s'))
            ->where('fin', '>', $debut->format('Y-m-d H:i:s'))
            ->where(function ($query) use ($validated): void {
                $query->where('enseignant_id', $validated['enseignant_id'])
                    ->orWhere('groupe_id', $validated['groupe_id'])
                    ->orWhere('salle_id', $validated['salle_id']);
            })
            ->exists();
        abort_if($chevauchementPlanifie, 409, 'Le créneau choisi entre en conflit avec une autre proposition hebdomadaire.');

        $seanceId = DB::table('edt_hebdomadaire_seance')->insertGetId([
            'edt_id' => $planning->id,
            'cours_id' => $validated['cours_id'],
            'groupe_id' => $validated['groupe_id'],
            'enseignant_id' => $validated['enseignant_id'],
            'salle_id' => $validated['salle_id'],
            'debut' => $debut->format('Y-m-d H:i:s'),
            'fin' => $fin->format('Y-m-d H:i:s'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'message' => 'Séance ajoutée à la proposition.',
            'data' => DB::table('edt_hebdomadaire_seance')->where('id', $seanceId)->first(),
        ], 201);
    }

    public function supprimerSeance(int $id, int $seanceId): JsonResponse
    {
        $planning = DB::table('edt_hebdomadaire')->where('id', $id)->first();
        abort_if($planning === null, 404, 'Emploi du temps introuvable.');
        abort_unless($planning->statut === 'PREPARATION', 409, 'Cet emploi du temps n’est plus modifiable.');

        $deleted = DB::table('edt_hebdomadaire_seance')
            ->where('edt_id', $id)
            ->where('id', $seanceId)
            ->delete();
        abort_if($deleted === 0, 404, 'Séance proposée introuvable.');

        return response()->json(['message' => 'Séance retirée de la proposition.']);
    }

    public function soumettre(int $id): JsonResponse
    {
        $planning = DB::table('edt_hebdomadaire')->where('id', $id)->first();
        abort_if($planning === null, 404, 'Emploi du temps introuvable.');
        abort_unless($planning->statut === 'PREPARATION', 409, 'Seul un emploi du temps en préparation peut être soumis.');
        abort_if(
            ! DB::table('edt_hebdomadaire_seance')->where('edt_id', $id)->exists(),
            422,
            'Ajoutez au moins une séance avant de soumettre cet emploi du temps.'
        );

        DB::table('edt_hebdomadaire')->where('id', $id)->update([
            'statut' => 'A_VALIDER',
            'updated_at' => now(),
        ]);

        return response()->json(['message' => 'Emploi du temps soumis à la validation de la scolarité.']);
    }

    public function valider(int $id, Request $request): JsonResponse
    {
        $planning = DB::table('edt_hebdomadaire')->where('id', $id)->first();
        abort_if($planning === null, 404, 'Emploi du temps introuvable.');
        abort_unless($planning->statut === 'A_VALIDER', 409, 'Cet emploi du temps n’est pas en attente de validation.');

        DB::table('edt_hebdomadaire')->where('id', $id)->update([
            'statut' => 'VALIDEE',
            'valide_par' => $request->user()->id,
            'valide_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json(['message' => 'Emploi du temps validé par la scolarité.']);
    }

    public function publier(int $id, Request $request): JsonResponse
    {
        try {
            $resultat = DB::transaction(function () use ($id, $request): array {
                $planning = DB::table('edt_hebdomadaire')->where('id', $id)->lockForUpdate()->first();
                abort_if($planning === null, 404, 'Emploi du temps introuvable.');
                abort_unless($planning->statut === 'VALIDEE', 409, 'Seul un emploi du temps validé peut être publié.');

                $propositions = DB::table('edt_hebdomadaire_seance')->where('edt_id', $id)->orderBy('debut')->get();
                abort_if($propositions->isEmpty(), 422, 'Cet emploi du temps ne contient aucune séance à publier.');

                foreach ($propositions as $proposition) {
                    $debut = $proposition->debut;
                    $fin = $proposition->fin;
                    $conflit = DB::table('seance')
                        ->where('debut', '<', $fin)
                        ->where('fin', '>', $debut)
                        ->where(function ($query) use ($proposition): void {
                            $query->where('enseignant_id', $proposition->enseignant_id)
                                ->orWhere('groupe_id', $proposition->groupe_id)
                                ->orWhere('salle_id', $proposition->salle_id);
                        })
                        ->exists();
                    abort_if($conflit, 409, 'Une séance déjà publiée est en conflit avec cette proposition.');
                }

                $maintenant = now();
                foreach ($propositions as $proposition) {
                    DB::table('seance')->insert([
                        'cours_id' => $proposition->cours_id,
                        'groupe_id' => $proposition->groupe_id,
                        'enseignant_id' => $proposition->enseignant_id,
                        'salle_id' => $proposition->salle_id,
                        'debut' => $proposition->debut,
                        'fin' => $proposition->fin,
                        'edt_seance_id' => $proposition->id,
                    ]);
                }

                DB::table('edt_hebdomadaire')->where('id', $id)->update([
                    'statut' => 'PUBLIEE',
                    'publie_par' => $request->user()->id,
                    'publie_at' => $maintenant,
                    'updated_at' => $maintenant,
                ]);

                return ['nombre_seances' => $propositions->count()];
            });
        } catch (QueryException $exception) {
            report($exception);

            return response()->json([
                'message' => 'Publication impossible : un conflit de salle, de groupe ou d’enseignant a été détecté.',
            ], 409);
        }

        return response()->json([
            'message' => 'Emploi du temps publié.',
            'nombre_seances' => $resultat['nombre_seances'],
        ]);
    }

    /** @return array<string,mixed> */
    private function chargerPlanning(int $id): array
    {
        $planning = DB::table('edt_hebdomadaire as edt')
            ->join('filiere as f', 'f.id', '=', 'edt.filiere_id')
            ->join('annee_universitaire as a', 'a.id', '=', 'edt.annee_id')
            ->leftJoin('utilisateur as prepareur', 'prepareur.id', '=', 'edt.prepare_par')
            ->leftJoin('utilisateur as validateur', 'validateur.id', '=', 'edt.valide_par')
            ->leftJoin('utilisateur as editeur', 'editeur.id', '=', 'edt.publie_par')
            ->where('edt.id', $id)
            ->select([
                'edt.id',
                'edt.filiere_id',
                'f.code as filiere_code',
                'f.nom as filiere_nom',
                'edt.annee_id',
                'a.libelle as annee',
                'edt.semaine_debut',
                'edt.statut',
                'edt.prepare_par',
                'prepareur.email as prepareur_email',
                'edt.valide_par',
                'validateur.email as validateur_email',
                'edt.valide_at',
                'edt.publie_par',
                'editeur.email as editeur_email',
                'edt.publie_at',
            ])
            ->first();
        abort_if($planning === null, 404, 'Emploi du temps introuvable.');

        $planning->seances = DB::table('edt_hebdomadaire_seance as es')
            ->join('cours as c', 'c.id', '=', 'es.cours_id')
            ->join('groupe as g', 'g.id', '=', 'es.groupe_id')
            ->join('enseignant as e', 'e.id', '=', 'es.enseignant_id')
            ->join('salle as s', 's.id', '=', 'es.salle_id')
            ->where('es.edt_id', $id)
            ->select([
                'es.id',
                'es.cours_id',
                'c.code as cours_code',
                'c.intitule as cours_intitule',
                'es.groupe_id',
                'g.nom as groupe_nom',
                'es.enseignant_id',
                'e.nom as enseignant_nom',
                'e.prenom as enseignant_prenom',
                'es.salle_id',
                's.nom as salle_nom',
                'es.debut',
                'es.fin',
            ])
            ->orderBy('es.debut')
            ->get();

        return (array) $planning;
    }

    private function validerLundi(string $date): void
    {
        if ((new DateTimeImmutable($date))->format('N') !== '1') {
            throw ValidationException::withMessages([
                'semaine_debut' => 'La date de début de semaine doit être un lundi.',
            ]);
        }
    }
}
