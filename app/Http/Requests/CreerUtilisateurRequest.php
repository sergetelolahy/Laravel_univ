<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Domain\Identity\Enums\Role;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class CreerUtilisateurRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $roleRules = [
            'required',
            'string',
            Rule::in(array_map(static fn (Role $role): string => $role->value, Role::cases())),
        ];

        $rules = [
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:8'],
            'role' => $roleRules,
            'actif' => ['sometimes', 'boolean'],
        ];

        if (($this->input('role') ?? null) === Role::ETUDIANT->value) {
            $rules = array_merge($rules, [
                'matricule' => ['required', 'string', 'max:50'],
                'nom' => ['required', 'string', 'max:255'],
                'prenom' => ['required', 'string', 'max:255'],
                'filiere_id' => ['required', 'integer', 'exists:filiere,id'],
                'date_naissance' => ['nullable', 'date'],
            ]);
        }

        if (($this->input('role') ?? null) === Role::ENSEIGNANT->value) {
            $rules = array_merge($rules, [
                'nom' => ['required', 'string', 'max:255'],
                'prenom' => ['required', 'string', 'max:255'],
                'specialite' => ['nullable', 'string', 'max:255'],
            ]);
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'email.required' => 'L’email est obligatoire.',
            'email.email' => 'L’email est invalide.',
            'password.required' => 'Le mot de passe est obligatoire.',
            'password.min' => 'Le mot de passe doit contenir au moins 8 caractères.',
            'role.required' => 'Le rôle est obligatoire.',
            'role.in' => 'Le rôle n’est pas valide.',
            'matricule.required' => 'Le matricule est obligatoire pour un étudiant.',
            'nom.required' => 'Le nom est obligatoire.',
            'prenom.required' => 'Le prénom est obligatoire.',
            'filiere_id.required' => 'La filière est obligatoire pour un étudiant.',
            'filiere_id.exists' => 'La filière sélectionnée est introuvable.',
        ];
    }
}
