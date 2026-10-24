<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class FiltreSeancesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string,mixed> */
    public function rules(): array
    {
        return [
            'groupe_id' => ['nullable', 'integer'],
            'enseignant_id' => ['nullable', 'integer'],
            'salle_id' => ['nullable', 'integer'],
            'cours_id' => ['nullable', 'integer'],
            'du' => ['nullable', 'date_format:Y-m-d'],
            'au' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:du'],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }

    /** @return array<string,string> */
    public function messages(): array
    {
        return [
            'integer' => 'Le champ :attribute doit être un entier.',
            'date_format' => 'Le champ :attribute doit être au format AAAA-MM-JJ.',
            'au.after_or_equal' => 'La date de fin de période doit être postérieure ou égale au début.',
        ];
    }

    /** @return array<string,mixed> filtres renseignés, sans la pagination */
    public function filtres(): array
    {
        $donnees = $this->validated();
        unset($donnees['page']);

        return array_filter($donnees, static fn ($v) => $v !== null && $v !== '');
    }
}
