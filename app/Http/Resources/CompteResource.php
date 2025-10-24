<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CompteResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'numeroCompte' => $this->numero_compte,
            'titulaire' => $this->client->titulaire,
            'type' => $this->type,
            'solde' => $this->solde,
            'devise' => $this->devise,
            'dateCreation' => $this->created_at,
            'statut' => $this->statut,
            'motifBlocage' => $this->when($this->statut === 'bloque', $this->motif_blocage),
            'dateDebutBlocage' => $this->when($this->statut === 'bloque', $this->date_debut_blocage),
            'dateFinBlocage' => $this->when($this->statut === 'bloque', $this->date_fin_blocage),
            'metadata' => [
                'derniereModification' => $this->derniere_modification,
                'version' => $this->version
            ]
        ];
    }
}
