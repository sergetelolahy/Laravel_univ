<?php

declare(strict_types=1);

namespace App\Domain\Scolarite\Entities;

readonly class Etudiant
{
    public function __construct(
        public int $id,
        public int $utilisateurId,
        public string $matricule,
        public string $nom,
        public string $prenom,
        public ?int $filiereId,
        public ?string $filiereCode = null,
        public ?string $filiereNom = null,
    ) {
    }

    public function resumeFiliere(): ?array
    {
        if ($this->filiereId === null || $this->filiereCode === null || $this->filiereNom === null) {
            return null;
        }

        return [
            'id' => $this->filiereId,
            'code' => $this->filiereCode,
            'nom' => $this->filiereNom,
        ];
    }
}
