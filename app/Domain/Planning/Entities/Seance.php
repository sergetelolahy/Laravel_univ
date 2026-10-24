<?php

declare(strict_types=1);

namespace App\Domain\Planning\Entities;

final class Seance
{
    /**
     * @param array{id:int,code:string,intitule:string} $cours
     * @param array{id:int,nom:string} $groupe
     * @param array{id:int,nom:string,prenom:string} $enseignant
     * @param array{id:int,nom:string,capacite:int} $salle
     */
    public function __construct(
        public readonly int $id,
        public readonly \DateTimeImmutable $debut,
        public readonly \DateTimeImmutable $fin,
        public readonly array $cours,
        public readonly array $groupe,
        public readonly array $enseignant,
        public readonly array $salle,
    ) {
    }
}
