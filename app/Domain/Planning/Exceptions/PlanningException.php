<?php

declare(strict_types=1);

namespace App\Domain\Planning\Exceptions;

/**
 * Base des erreurs métier du module Planning.
 * statutHttp() indique le code HTTP à renvoyer (converti dans bootstrap/app.php).
 */
abstract class PlanningException extends \DomainException
{
    abstract public function statutHttp(): int;
}
