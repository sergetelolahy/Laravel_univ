<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreNotesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'notes' => ['required', 'array', 'min:1'],
            'notes.*.etudiant_id' => ['required', 'integer', 'distinct', 'exists:etudiant,id'],
            'notes.*.valeur' => ['required', 'numeric', 'between:0,20'],
        ];
    }
}
