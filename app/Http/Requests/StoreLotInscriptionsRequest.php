<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreLotInscriptionsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'inscriptions' => ['required', 'array', 'min:1'],
            'inscriptions.*.etudiant_id' => ['required', 'integer', 'gt:0', 'exists:etudiant,id'],
            'inscriptions.*.cours_id' => ['required', 'integer', 'gt:0', 'exists:cours,id'],
            'inscriptions.*.annee_id' => ['required', 'integer', 'gt:0', 'exists:annee_universitaire,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'inscriptions.required' => 'La liste des inscriptions est obligatoire.',
            'inscriptions.min' => 'La liste des inscriptions ne peut pas être vide.',
            'inscriptions.*.etudiant_id.required' => 'L’étudiant est obligatoire pour chaque inscription.',
            'inscriptions.*.cours_id.required' => 'Le cours est obligatoire pour chaque inscription.',
            'inscriptions.*.annee_id.required' => 'L’année est obligatoire pour chaque inscription.',
        ];
    }
}
