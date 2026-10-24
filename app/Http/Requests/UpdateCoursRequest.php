<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateCoursRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => ['sometimes', 'required', 'string', 'max:50'],
            'intitule' => ['sometimes', 'required', 'string', 'max:255'],
            'semestre_id' => ['sometimes', 'required', 'integer', 'exists:semestre,id'],
            'coefficient' => ['sometimes', 'required', 'numeric', 'gt:0'],
            'credits' => ['sometimes', 'required', 'integer', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'code.required' => 'Le code du cours est obligatoire.',
            'intitule.required' => 'L’intitulé du cours est obligatoire.',
            'semestre_id.required' => 'Le semestre est obligatoire.',
            'coefficient.required' => 'Le coefficient est obligatoire.',
            'coefficient.gt' => 'Le coefficient doit être strictement positif.',
            'credits.required' => 'Les crédits sont obligatoires.',
        ];
    }
}
