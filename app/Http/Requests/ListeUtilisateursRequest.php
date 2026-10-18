<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Domain\Identity\Enums\Role;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class ListeUtilisateursRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'role' => ['sometimes', 'nullable', 'string', Rule::in(array_map(static fn (Role $role): string => $role->value, Role::cases()))],
            'page' => ['sometimes', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'role.in' => 'Le rôle n’est pas valide.',
            'page.min' => 'La page doit être supérieure ou égale à 1.',
            'per_page.min' => 'Le nombre par page doit être supérieur ou égal à 1.',
            'per_page.max' => 'Le nombre par page ne peut pas dépasser 100.',
        ];
    }
}
