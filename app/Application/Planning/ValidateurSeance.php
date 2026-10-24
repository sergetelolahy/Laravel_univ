<?php

declare(strict_types=1);

namespace App\Application\Planning;

use App\Domain\Planning\Exceptions\CapaciteSalleInsuffisanteException;
use App\Domain\Planning\Exceptions\CoursHorsFiliereException;
use App\Domain\Planning\Exceptions\CreneauInvalideException;
use App\Domain\Planning\Exceptions\EnseignantNonAffecteException;
use App\Domain\Planning\Exceptions\ReferenceIntrouvableException;
use App\Domain\Planning\Repositories\PlanningContexte;

/**
 * Règles métier d'une séance, communes à la planification et à la modification.
 * Les conflits (salle, enseignant, groupe) sont détectés par le repository (verrous + triggers).
 */
final class ValidateurSeance
{
    public function __construct(private readonly PlanningContexte $contexte)
    {
    }

    public function valider(DonneesSeance $d): void
    {
        if ($d->fin <= $d->debut) {
            throw new CreneauInvalideException('La fin doit être postérieure au début.');
        }
        if ($d->debut->format('Y-m-d') !== $d->fin->format('Y-m-d')) {
            throw new CreneauInvalideException('Une séance doit se dérouler sur une seule journée.');
        }
        $minutes = intdiv($d->fin->getTimestamp() - $d->debut->getTimestamp(), 60);
        if ($minutes < 30 || $minutes > 360) {
            throw new CreneauInvalideException("La durée d'une séance doit être comprise entre 30 minutes et 6 heures.");
        }

        $groupe = $this->contexte->groupe($d->groupeId)
            ?? throw new ReferenceIntrouvableException('Groupe inexistant.');
        $salle = $this->contexte->salle($d->salleId)
            ?? throw new ReferenceIntrouvableException('Salle inexistante.');
        $filiereCours = $this->contexte->filiereDuCours($d->coursId)
            ?? throw new ReferenceIntrouvableException('Cours inexistant.');
        $plage = $this->contexte->anneePlage($groupe['annee_id'])
            ?? throw new ReferenceIntrouvableException('Année universitaire du groupe inexistante.');

        $jour = $d->debut->format('Y-m-d');
        if ($jour < $plage['date_debut'] || $jour > $plage['date_fin']) {
            throw new CreneauInvalideException(sprintf(
                "La séance doit se situer dans la période de l'année du groupe (du %s au %s).",
                $plage['date_debut'],
                $plage['date_fin']
            ));
        }

        if ($filiereCours !== $groupe['filiere_id']) {
            throw new CoursHorsFiliereException();
        }

        if (! $this->contexte->enseignantAffecte($d->enseignantId, $d->coursId, $groupe['annee_id'])) {
            throw new EnseignantNonAffecteException();
        }

        if ($groupe['effectif'] > $salle['capacite']) {
            throw new CapaciteSalleInsuffisanteException(sprintf(
                "La salle %s (%d places) est trop petite pour ce groupe (%d étudiants).",
                $salle['nom'],
                $salle['capacite'],
                $groupe['effectif']
            ));
        }
    }
}
