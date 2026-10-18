<?php

declare(strict_types=1);

namespace App\Domain\Identity\Enums;

enum Role: string
{
    case ADMIN = 'ADMIN';
    case SCOLARITE = 'SCOLARITE';
    case ENSEIGNANT = 'ENSEIGNANT';
    case ETUDIANT = 'ETUDIANT';
}
