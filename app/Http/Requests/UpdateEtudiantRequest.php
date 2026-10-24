<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateEtudiantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nom' => ['sometimes', 'string', 'max:255'],
            'prenom' => ['sometimes', 'string', 'max:255'],
            'matricule' => ['sometimes', 'string', 'max:50'],
            'filiere_id' => ['sometimes', 'nullable', 'integer', 'exists:filiere,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'nom.string' => 'Le nom est invalide.',
            'prenom.string' => 'Le prénom est invalide.',
            'matricule.string' => 'La matricule est invalide.',
            'filiere_id.integer' => 'La filière est invalide.',
        ];
    }
}
