<?php

namespace Database\Factories;

use App\Models\Client;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ClientFactory extends Factory
{
    protected $model = Client::class;

    protected static ?string $fakerLocale = 'fr_SN';

    public function definition(): array
    {
        return [
            'id' => (string) Str::uuid(),
            'titulaire' => $this->faker->name(),
            'nci' => $this->generateValidNci(),
            'email' => $this->faker->unique()->safeEmail(),
            'telephone' => $this->generateValidSenegalesePhone(),
            'adresse' => $this->faker->city() . ', Sénégal',
            'password' => bcrypt('password123'),
            'code' => Client::generateCode(),
        ];
    }

    /**
     * Générer un numéro CNI sénégalais valide (13 chiffres commençant par 1 ou 2)
     */
    private function generateValidNci(): string
    {
        do {
            $firstDigit = $this->faker->randomElement(['1', '2']);
            $nci = $firstDigit . $this->faker->unique()->numerify('############');
        } while (strlen($nci) !== 13);

        return $nci;
    }

    /**
     * Générer un numéro de téléphone sénégalais valide
     */
    private function generateValidSenegalesePhone(): string
    {
        $prefixes = ['77', '78', '76', '70', '75', '33', '32'];
        $prefix = $this->faker->randomElement($prefixes);

        do {
            $number = '+221' . $prefix . $this->faker->unique()->numerify('#######');
        } while (strlen($number) !== 13);

        return $number;
    }

    /**
     * État : client vérifié
     */
    public function verified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => now(),
        ]);
    }

    /**
     * État : client avec code spécifique
     */
    public function withCode(string $code): static
    {
        return $this->state(fn (array $attributes) => [
            'code' => $code,
        ]);
    }
}
