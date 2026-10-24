<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateSemestreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'filiere_id' => ['sometimes', 'required', 'integer', 'exists:filiere,id'],
            'numero' => ['sometimes', 'required', 'integer', 'min:1', 'max:10'],
        ];
    }

    public function messages(): array
    {
        return [
            'filiere_id.required' => 'La filière est obligatoire.',
            'numero.required' => 'Le numéro de semestre est obligatoire.',
            'numero.min' => 'Le numéro du semestre doit être supérieur ou égal à 1.',
            'numero.max' => 'Le numéro du semestre ne peut pas dépasser 10.',
        ];
    }
}
