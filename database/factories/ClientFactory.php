<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ClientFactory extends Factory
{
    protected $model = \App\Models\Client::class;

    protected static ?string $fakerLocale = 'fr_SN';

    public function definition(): array
    {
        return [
            'id' => (string) Str::uuid(),
            'titulaire' => $this->faker->name(),
            'nci' => $this->faker->unique()->numerify('#############'),
            'email' => $this->faker->unique()->safeEmail(),
            'telephone' => $this->faker->unique()->numerify('+22177#######'),
            'adresse' => $this->faker->city() . ', Sénégal',
            'password' => bcrypt('password'),
            'code' => $this->faker->numerify('###'),
        ];
    }
}
