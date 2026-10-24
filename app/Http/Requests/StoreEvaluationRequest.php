<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreEvaluationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'cours_id' => ['required', 'integer', 'exists:cours,id'],
            'annee_id' => ['required', 'integer', 'exists:annee_universitaire,id'],
            'type' => ['required', 'string', 'max:30'],
            'ponderation' => ['required', 'numeric', 'gt:0', 'max:9999.99'],
            'date_eval' => ['nullable', 'date'],
        ];
    }
}
