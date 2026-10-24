<?php

declare(strict_types=1);

namespace App\Application\Referentiel\Filiere;

use App\Domain\Referentiel\Exceptions\ConflitException;
use App\Domain\Referentiel\Exceptions\DonneesInvalidesException;
use App\Domain\Referentiel\Exceptions\RessourceIntrouvableException;
use App\Domain\Referentiel\Repositories\FiliereRepositoryInterface;

final class ModifierFiliere
{
    public function __construct(private readonly FiliereRepositoryInterface $repository)
    {
    }

    /**
     * @param array{code?:string, nom?:string} $donnees
     */
    public function execute(int $id, array $donnees): array
    {
        $filiere = $this->repository->trouverParId($id);
        if ($filiere === null) {
            throw new RessourceIntrouvableException('Filiere', $id);
        }

        $code = trim((string) ($donnees['code'] ?? $filiere->code));
        $nom = trim((string) ($donnees['nom'] ?? $filiere->nom));

        if ($code === '' || $nom === '') {
            throw new DonneesInvalidesException('Le code et le nom de la filière sont obligatoires.');
        }

        if ($this->repository->existeCode($code, $id)) {
            throw new ConflitException('Une filière avec ce code existe déjà.');
        }

        if ($this->repository->existeNom($nom, $id)) {
            throw new ConflitException('Une filière avec ce nom existe déjà.');
        }

        $updated = $this->repository->modifier($id, [
            'code' => $code,
            'nom' => $nom,
        ]);

        return [
            'id' => $updated->id,
            'code' => $updated->code,
            'nom' => $updated->nom,
        ];
    }
}
