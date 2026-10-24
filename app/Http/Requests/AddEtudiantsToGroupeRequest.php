<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class AddEtudiantsToGroupeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'etudiant_ids' => ['required', 'array'],
            'etudiant_ids.*' => ['integer', 'gt:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'etudiant_ids.required' => 'La liste des étudiants est obligatoire.',
            'etudiant_ids.array' => 'La liste des étudiants doit être un tableau.',
            'etudiant_ids.*.integer' => 'Chaque étudiant doit avoir un identifiant valide.',
        ];
    }
}
