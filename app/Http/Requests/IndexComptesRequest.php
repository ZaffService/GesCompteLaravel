<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class IndexComptesRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'page' => 'nullable|integer|min:1',
            'limit' => 'nullable|integer|min:1|max:100',
            'type' => 'nullable|string|in:epargne,cheque',
            'statut' => 'nullable|string|in:actif,bloque,ferme',
            'search' => 'nullable|string|max:255',
            'sort' => 'nullable|string|in:dateCreation,solde,titulaire',
            'order' => 'nullable|string|in:asc,desc',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'page.integer' => 'Le numéro de page doit être un entier',
            'page.min' => 'Le numéro de page doit être au minimum 1',
            'limit.integer' => 'La limite doit être un entier',
            'limit.min' => 'La limite doit être au minimum 1',
            'limit.max' => 'La limite ne peut pas dépasser 100',
            'type.in' => 'Le type doit être soit epargne soit cheque',
            'statut.in' => 'Le statut doit être actif, bloque ou ferme',
            'search.max' => 'La recherche ne peut pas dépasser 255 caractères',
            'sort.in' => 'Le tri doit être dateCreation, solde ou titulaire',
            'order.in' => 'L\'ordre doit être asc ou desc'
        ];
    }

    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'error' => [
                'code' => 'VALIDATION_ERROR',
                'message' => 'Les paramètres de requête sont invalides',
                'details' => $validator->errors()
            ]
        ], 422));
    }
}
