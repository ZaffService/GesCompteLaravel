<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreClientRequest extends FormRequest
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
            'titulaire' => ['required', 'string', 'max:255'],
            'nci' => [
                'nullable',
                'string',
                'size:13',
                'regex:/^[12]\d{12}$/',
                Rule::unique('clients', 'nci')
            ],
            'email' => [
                'required',
                'email:rfc,dns',
                'max:255',
                Rule::unique('clients', 'email')
            ],
            'telephone' => [
                'required',
                'string',
                'regex:/^\+221(77|78|76|70|75|33|32)\d{7}$/',
                Rule::unique('clients', 'telephone')
            ],
            'adresse' => ['nullable', 'string', 'max:500'],
            'type' => ['required', 'string', 'in:cheque,epargne'],
            'soldeInitial' => ['required', 'numeric', 'min:10000'],
            'devise' => ['required', 'string', 'in:FCFA,XOF,EUR,USD'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'titulaire.required' => 'Le nom du titulaire est obligatoire.',
            'titulaire.string' => 'Le titulaire doit être une chaîne de caractères.',
            'titulaire.max' => 'Le nom du titulaire ne peut pas dépasser 255 caractères.',

            'nci.size' => 'Le numéro CNI doit contenir exactement 13 chiffres.',
            'nci.regex' => 'Le numéro CNI doit commencer par 1 ou 2 et contenir 13 chiffres.',
            'nci.unique' => 'Ce numéro CNI est déjà utilisé.',

            'email.required' => 'L\'adresse email est obligatoire.',
            'email.email' => 'L\'adresse email doit être valide.',
            'email.max' => 'L\'email ne peut pas dépasser 255 caractères.',
            'email.unique' => 'Cette adresse email est déjà utilisée.',

            'telephone.required' => 'Le numéro de téléphone est obligatoire.',
            'telephone.regex' => 'Le numéro de téléphone doit être un numéro sénégalais valide (+221XXXXXXXXX).',
            'telephone.unique' => 'Ce numéro de téléphone est déjà utilisé.',

            'adresse.string' => 'L\'adresse doit être une chaîne de caractères.',
            'adresse.max' => 'L\'adresse ne peut pas dépasser 500 caractères.',

            'type.required' => 'Le type de compte est obligatoire.',
            'type.in' => 'Le type de compte doit être soit "cheque" soit "epargne".',

            'soldeInitial.required' => 'Le solde initial est obligatoire.',
            'soldeInitial.numeric' => 'Le solde initial doit être un nombre.',
            'soldeInitial.min' => 'Le solde initial doit être d\'au moins 10 000 FCFA.',

            'devise.required' => 'La devise est obligatoire.',
            'devise.in' => 'La devise doit être FCFA, XOF, EUR ou USD.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'titulaire' => 'nom du titulaire',
            'nci' => 'numéro CNI',
            'telephone' => 'numéro de téléphone',
            'soldeInitial' => 'solde initial',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Nettoyer le numéro de téléphone
        if ($this->has('telephone')) {
            $telephone = preg_replace('/\s+/', '', $this->telephone);
            $this->merge(['telephone' => $telephone]);
        }

        // Nettoyer le CNI
        if ($this->has('nci')) {
            $nci = preg_replace('/\s+/', '', $this->nci);
            $this->merge(['nci' => $nci]);
        }
    }
}
