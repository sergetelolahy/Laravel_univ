<?php

declare(strict_types=1);

namespace App\Application\Referentiel\AnneeUniversitaire;

use App\Domain\Referentiel\Exceptions\ConflitException;
use App\Domain\Referentiel\Exceptions\DonneesInvalidesException;
use App\Domain\Referentiel\Repositories\AnneeUniversitaireRepositoryInterface;

final class CreerAnneeUniversitaire
{
    public function __construct(private readonly AnneeUniversitaireRepositoryInterface $repository)
    {
    }

    /**
     * @param array{libelle:string, date_debut:string, date_fin:string, courante?:bool} $donnees
     */
    public function execute(array $donnees): array
    {
        $libelle = trim((string) ($donnees['libelle'] ?? ''));
        $dateDebut = trim((string) ($donnees['date_debut'] ?? ''));
        $dateFin = trim((string) ($donnees['date_fin'] ?? ''));

        $this->valider($libelle, $dateDebut, $dateFin);

        if ($this->repository->existeLibelle($libelle)) {
            throw new ConflitException('Une année universitaire avec ce libellé existe déjà.');
        }

        $annee = $this->repository->creer([
            'libelle' => $libelle,
            'date_debut' => $dateDebut,
            'date_fin' => $dateFin,
            'courante' => (bool) ($donnees['courante'] ?? false),
        ]);

        return [
            'id' => $annee->id,
            'libelle' => $annee->libelle,
            'date_debut' => $annee->dateDebut->format('Y-m-d'),
            'date_fin' => $annee->dateFin->format('Y-m-d'),
            'courante' => $annee->courante,
        ];
    }

    private function valider(string $libelle, string $dateDebut, string $dateFin): void
    {
        if ($libelle === '' || $dateDebut === '' || $dateFin === '') {
            throw new DonneesInvalidesException('Le libellé, la date de début et la date de fin sont obligatoires.');
        }

        if (! preg_match('/^\d{4}-\d{4}$/', $libelle)) {
            throw new DonneesInvalidesException('Le libellé doit avoir le format AAAA-AAAA.');
        }

        $anneeDebut = (int) substr($libelle, 0, 4);
        $anneeFin = (int) substr($libelle, 5, 4);
        if ($anneeFin !== $anneeDebut + 1) {
            throw new DonneesInvalidesException('La deuxième année du libellé doit être égale à la première + 1.');
        }

        $debut = new \DateTimeImmutable($dateDebut);
        $fin = new \DateTimeImmutable($dateFin);
        if ($fin <= $debut) {
            throw new DonneesInvalidesException('La date de fin doit être strictement supérieure à la date de début.');
        }
    }
}
