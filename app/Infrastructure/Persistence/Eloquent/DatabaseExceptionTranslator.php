<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent;

use App\Domain\Referentiel\Exceptions\ConflitException;
use App\Domain\Referentiel\Exceptions\DonneesInvalidesException;
use App\Domain\Referentiel\Exceptions\RessourceUtiliseeException;
use App\Domain\Scolarite\Exceptions\InscriptionDejaExistanteException;
use Illuminate\Database\QueryException;

final class DatabaseExceptionTranslator
{
    public static function translate(QueryException $exception): never
    {
        $code = (int) ($exception->errorInfo[1] ?? 0);
        $message = strtolower((string) ($exception->errorInfo[2] ?? $exception->getMessage()));

        switch ($code) {
            case 1062:
                if (str_contains($message, 'inscription_cours') || str_contains($message, 'etudiant_id_cours_id_annee_id_unique')) {
                    throw new InscriptionDejaExistanteException();
                }

                throw new ConflitException('Cette ressource existe déjà.');
            case 1451:
                throw new RessourceUtiliseeException('Suppression impossible : des données en dépendent');
            case 1452:
                throw new DonneesInvalidesException('Une clé étrangère référencée est introuvable.');
            case 4025:
            case 3819:
                throw new DonneesInvalidesException('Les données ne respectent pas les contraintes métier.');
            default:
                throw $exception;
        }
    }
}
