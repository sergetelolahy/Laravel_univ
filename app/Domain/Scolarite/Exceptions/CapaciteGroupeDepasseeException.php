<?php

declare(strict_types=1);

namespace App\Domain\Scolarite\Exceptions;

final class CapaciteGroupeDepasseeException extends DomainException
{
    public function __construct()
    {
        parent::__construct('La capacité maximale du groupe est dépassée.');
    }
}
