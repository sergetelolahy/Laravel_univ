<?php

declare(strict_types=1);

namespace App\Domain\Scolarite\Entities;

readonly class Inscription
{
    public function __construct(
        public int $id,
        public int $etudiantId,
        public int $coursId,
        public int $anneeId,
        public string $dateInscription,
        public ?string $etudiantMatricule = null,
        public ?string $etudiantNom = null,
        public ?string $etudiantPrenom = null,
        public ?string $coursCode = null,
        public ?string $coursIntitule = null,
        public ?int $coursSemestreId = null,
        public ?string $anneeLibelle = null,
    ) {
    }

    public function resumeEtudiant(): ?array
    {
        if ($this->etudiantMatricule === null || $this->etudiantNom === null || $this->etudiantPrenom === null) {
            return null;
        }

        return [
            'id' => $this->etudiantId,
            'matricule' => $this->etudiantMatricule,
            'nom' => $this->etudiantNom,
            'prenom' => $this->etudiantPrenom,
        ];
    }

    public function resumeCours(): ?array
    {
        if ($this->coursCode === null || $this->coursIntitule === null) {
            return null;
        }

        return [
            'id' => $this->coursId,
            'code' => $this->coursCode,
            'intitule' => $this->coursIntitule,
            'semestre_id' => $this->coursSemestreId,
        ];
    }

    public function resumeAnnee(): ?array
    {
        if ($this->anneeId <= 0 || $this->anneeLibelle === null) {
            return null;
        }

        return [
            'id' => $this->anneeId,
            'libelle' => $this->anneeLibelle,
        ];
    }
}
