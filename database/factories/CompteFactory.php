<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\Compte;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Compte>
 */
class CompteFactory extends Factory
{
    protected $model = Compte::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => (string) Str::uuid(),
            'numero_compte' => Compte::generateNumeroCompte(),
            'client_id' => Client::factory(),
            'type' => $this->faker->randomElement(['epargne', 'cheque']),
            'solde' => $this->faker->randomFloat(2, 10000, 1000000),
            'devise' => 'FCFA',
            'statut' => $this->faker->randomElement(['actif', 'bloque', 'ferme']),
            'motif_blocage' => $this->faker->optional(0.3)->sentence(),
            'date_debut_blocage' => $this->faker->optional(0.3)->dateTimeBetween('-1 month', 'now'),
            'date_fin_blocage' => $this->faker->optional(0.3)->dateTimeBetween('now', '+6 months'),
            'version' => 1,
            'derniere_modification' => now(),
        ];
    }

    /**
     * État : compte actif
     */
    public function actif(): static
    {
        return $this->state(fn (array $attributes) => [
            'statut' => 'actif',
            'motif_blocage' => null,
            'date_debut_blocage' => null,
            'date_fin_blocage' => null,
        ]);
    }

    /**
     * État : compte bloqué
     */
    public function bloque(): static
    {
        return $this->state(fn (array $attributes) => [
            'statut' => 'bloque',
            'motif_blocage' => $this->faker->sentence(),
            'date_debut_blocage' => $this->faker->dateTimeBetween('-1 month', '-1 day'),
            'date_fin_blocage' => $this->faker->dateTimeBetween('now', '+3 months'),
        ]);
    }

    /**
     * État : compte fermé
     */
    public function ferme(): static
    {
        return $this->state(fn (array $attributes) => [
            'statut' => 'ferme',
            'motif_blocage' => null,
            'date_debut_blocage' => null,
            'date_fin_blocage' => null,
        ]);
    }

    /**
     * État : compte épargne
     */
    public function epargne(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'epargne',
        ]);
    }

    /**
     * État : compte chèque
     */
    public function cheque(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'cheque',
        ]);
    }

    /**
     * État : avec solde spécifique
     */
    public function avecSolde(float $solde): static
    {
        return $this->state(fn (array $attributes) => [
            'solde' => $solde,
        ]);
    }

    /**
     * État : pour un client spécifique
     */
    public function pourClient(Client $client): static
    {
        return $this->state(fn (array $attributes) => [
            'client_id' => $client->id,
        ]);
    }
}
