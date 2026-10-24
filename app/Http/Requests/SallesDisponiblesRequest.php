<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class SallesDisponiblesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string,mixed> */
    public function rules(): array
    {
        return [
            'debut' => ['required', 'date_format:Y-m-d H:i:s'],
            'fin' => ['required', 'date_format:Y-m-d H:i:s', 'after:debut'],
            'capacite_min' => ['nullable', 'integer', 'min:0'],
        ];
    }

    /** @return array<string,string> */
    public function messages(): array
    {
        return [
            'required' => 'Le champ :attribute est obligatoire.',
            'date_format' => 'Le champ :attribute doit être au format AAAA-MM-JJ HH:MM:SS.',
            'fin.after' => 'La fin doit être postérieure au début.',
            'integer' => 'Le champ :attribute doit être un entier.',
            'min' => 'Le champ :attribute doit être positif.',
        ];
    }

    /** @return array<string,string> */
    public function attributes(): array
    {
        return ['debut' => 'début', 'fin' => 'fin', 'capacite_min' => 'capacité minimale'];
    }
}
