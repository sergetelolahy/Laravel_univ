<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreInscriptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'etudiant_id' => ['required', 'integer', 'gt:0', 'exists:etudiant,id'],
            'cours_id' => ['required', 'integer', 'gt:0', 'exists:cours,id'],
            'annee_id' => ['required', 'integer', 'gt:0', 'exists:annee_universitaire,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'etudiant_id.required' => 'L’étudiant est obligatoire.',
            'cours_id.required' => 'Le cours est obligatoire.',
            'annee_id.required' => 'L’année est obligatoire.',
        ];
    }
}
