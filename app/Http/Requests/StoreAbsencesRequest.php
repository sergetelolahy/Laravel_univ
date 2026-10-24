<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreAbsencesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'absences' => ['required', 'array', 'min:1'],
            'absences.*.etudiant_id' => ['required', 'integer', 'distinct', 'exists:etudiant,id'],
            'absences.*.justifiee' => ['sometimes', 'boolean'],
        ];
    }
}
