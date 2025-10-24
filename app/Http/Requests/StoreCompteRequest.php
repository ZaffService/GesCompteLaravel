<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCompteRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Autoriser tous les utilisateurs authentifiés
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'type' => ['required', 'string', 'in:epargne,cheque'],
            'soldeInitial' => ['required', 'numeric', 'min:10000', 'max:10000000'],
            'devise' => ['required', 'string', 'in:FCFA,XOF,EUR,USD'],
            'client' => ['required', 'array'],
            'client.id' => [
                'nullable',
                'string',
                'exists:clients,id',
                Rule::requiredIf(function () {
                    return !isset($this->client['email']);
                })
            ],
            'client.titulaire' => [
                'nullable',
                'string',
                'max:255',
                Rule::requiredIf(function () {
                    return !isset($this->client['id']);
                })
            ],
            'client.email' => [
                'nullable',
                'email:rfc,dns',
                'max:255',
                Rule::unique('clients', 'email'),
                Rule::requiredIf(function () {
                    return !isset($this->client['id']);
                })
            ],
            'client.telephone' => [
                'nullable',
                'string',
                'regex:/^\+221(77|78|76|70|75|33|32)\d{7}$/',
                Rule::unique('clients', 'telephone'),
                Rule::requiredIf(function () {
                    return !isset($this->client['id']);
                })
            ],
            'client.adresse' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'type.required' => 'Le type de compte est obligatoire.',
            'type.in' => 'Le type de compte doit être soit "epargne" soit "cheque".',

            'soldeInitial.required' => 'Le solde initial est obligatoire.',
            'soldeInitial.numeric' => 'Le solde initial doit être un nombre.',
            'soldeInitial.min' => 'Le solde initial doit être d\'au moins 10 000 FCFA.',
            'soldeInitial.max' => 'Le solde initial ne peut pas dépasser 10 000 000 FCFA.',

            'devise.required' => 'La devise est obligatoire.',
            'devise.in' => 'La devise doit être FCFA, XOF, EUR ou USD.',

            'client.required' => 'Les informations du client sont obligatoires.',
            'client.array' => 'Les informations du client doivent être un objet.',

            'client.id.exists' => 'Le client sélectionné n\'existe pas.',
            'client.id.required' => 'L\'ID du client est requis si vous ne fournissez pas ses informations.',

            'client.titulaire.required' => 'Le nom du titulaire est requis pour créer un nouveau client.',
            'client.titulaire.string' => 'Le nom du titulaire doit être une chaîne de caractères.',
            'client.titulaire.max' => 'Le nom du titulaire ne peut pas dépasser 255 caractères.',

            'client.email.required' => 'L\'adresse email est requise pour créer un nouveau client.',
            'client.email.email' => 'L\'adresse email doit être valide.',
            'client.email.max' => 'L\'email ne peut pas dépasser 255 caractères.',
            'client.email.unique' => 'Cette adresse email est déjà utilisée.',

            'client.telephone.required' => 'Le numéro de téléphone est requis pour créer un nouveau client.',
            'client.telephone.regex' => 'Le numéro de téléphone doit être un numéro sénégalais valide (+221XXXXXXXXX).',
            'client.telephone.unique' => 'Ce numéro de téléphone est déjà utilisé.',

            'client.adresse.string' => 'L\'adresse doit être une chaîne de caractères.',
            'client.adresse.max' => 'L\'adresse ne peut pas dépasser 500 caractères.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'type' => 'type de compte',
            'soldeInitial' => 'solde initial',
            'client.id' => 'ID du client',
            'client.titulaire' => 'nom du titulaire',
            'client.email' => 'adresse email',
            'client.telephone' => 'numéro de téléphone',
            'client.adresse' => 'adresse',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Nettoyer le numéro de téléphone si fourni
        if (isset($this->client['telephone'])) {
            $telephone = preg_replace('/\s+/', '', $this->client['telephone']);
            $this->merge([
                'client' => array_merge($this->client, ['telephone' => $telephone])
            ]);
        }
    }
}
