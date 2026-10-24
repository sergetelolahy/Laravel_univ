<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Application\Planning\DonneesSeance;
use Illuminate\Foundation\Http\FormRequest;

final class SeanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string,mixed> */
    public function rules(): array
    {
        return [
            'cours_id' => ['required', 'integer', 'exists:cours,id'],
            'groupe_id' => ['required', 'integer', 'exists:groupe,id'],
            'enseignant_id' => ['required', 'integer', 'exists:enseignant,id'],
            'salle_id' => ['required', 'integer', 'exists:salle,id'],
            'debut' => ['required', 'date_format:Y-m-d H:i:s'],
            'fin' => ['required', 'date_format:Y-m-d H:i:s', 'after:debut'],
        ];
    }

    /** @return array<string,string> */
    public function messages(): array
    {
        return [
            'required' => 'Le champ :attribute est obligatoire.',
            'integer' => 'Le champ :attribute doit être un entier.',
            'exists' => 'Le champ :attribute fait référence à un élément inexistant.',
            'date_format' => 'Le champ :attribute doit être au format AAAA-MM-JJ HH:MM:SS.',
            'fin.after' => 'La fin doit être postérieure au début.',
        ];
    }

    /** @return array<string,string> */
    public function attributes(): array
    {
        return [
            'cours_id' => 'cours',
            'groupe_id' => 'groupe',
            'enseignant_id' => 'enseignant',
            'salle_id' => 'salle',
            'debut' => 'début',
            'fin' => 'fin',
        ];
    }

    public function donnees(): DonneesSeance
    {
        $v = $this->validated();

        return new DonneesSeance(
            (int) $v['cours_id'],
            (int) $v['groupe_id'],
            (int) $v['enseignant_id'],
            (int) $v['salle_id'],
            \DateTimeImmutable::createFromFormat('!Y-m-d H:i:s', $v['debut']),
            \DateTimeImmutable::createFromFormat('!Y-m-d H:i:s', $v['fin']),
        );
    }
}
