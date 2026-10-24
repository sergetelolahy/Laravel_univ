<?php

declare(strict_types=1);

namespace App\Domain\Referentiel\Exceptions;

use App\Domain\Referentiel\Exceptions\DomainException;

final class RessourceUtiliseeException extends DomainException
{
    public function __construct(string $message = 'Suppression impossible : des données en dépendent')
    {
        parent::__construct($message);
    }
}
