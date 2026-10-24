<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreGroupeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nom' => ['required', 'string', 'max:255'],
            'filiere_id' => ['required', 'integer', 'exists:filiere,id'],
            'annee_id' => ['required', 'integer', 'exists:annee_universitaire,id'],
            'effectif_max' => ['required', 'integer', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            'nom.required' => 'Le nom du groupe est obligatoire.',
            'filiere_id.required' => 'La filière du groupe est obligatoire.',
            'annee_id.required' => 'L’année du groupe est obligatoire.',
            'effectif_max.required' => 'L’effectif maximal est obligatoire.',
            'effectif_max.min' => 'L’effectif maximal doit être supérieur à 0.',
        ];
    }
}
