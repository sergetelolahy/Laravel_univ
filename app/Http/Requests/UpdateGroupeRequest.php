<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateGroupeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nom' => ['sometimes', 'string', 'max:255'],
            'filiere_id' => ['sometimes', 'integer', 'exists:filiere,id'],
            'annee_id' => ['sometimes', 'integer', 'exists:annee_universitaire,id'],
            'effectif_max' => ['sometimes', 'integer', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            'nom.string' => 'Le nom du groupe est invalide.',
            'filiere_id.integer' => 'La filière du groupe est invalide.',
            'annee_id.integer' => 'L’année du groupe est invalide.',
            'effectif_max.min' => 'L’effectif maximal doit être supérieur à 0.',
        ];
    }
}
