<?php

namespace Database\Seeders;

use App\Models\Client;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ClientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Créer des clients de test avec des données réalistes
        $clients = [
            [
                'id' => '550e8400-e29b-41d4-a716-446655440001',
                'titulaire' => 'Amadou Diallo',
                'nci' => '1234567890123',
                'email' => 'amadou.diallo@example.com',
                'telephone' => '+221771234567',
                'adresse' => 'Dakar, Sénégal',
                'password' => bcrypt('password123'),
                'code' => 'ABC123',
            ],
            [
                'id' => '550e8400-e29b-41d4-a716-446655440002',
                'titulaire' => 'Fatou Sow',
                'nci' => '2234567890123',
                'email' => 'fatou.sow@example.com',
                'telephone' => '+221782345678',
                'adresse' => 'Thiès, Sénégal',
                'password' => bcrypt('password123'),
                'code' => 'DEF456',
            ],
            [
                'id' => '550e8400-e29b-41d4-a716-446655440003',
                'titulaire' => 'Moussa Ndiaye',
                'nci' => '1235567890123',
                'email' => 'moussa.ndiaye@example.com',
                'telephone' => '+221763456789',
                'adresse' => 'Saint-Louis, Sénégal',
                'password' => bcrypt('password123'),
                'code' => 'GHI789',
            ],
            [
                'id' => '550e8400-e29b-41d4-a716-446655440004',
                'titulaire' => 'Aïssatou Ba',
                'nci' => '2236567890123',
                'email' => 'aissatou.ba@example.com',
                'telephone' => '+221705678901',
                'adresse' => 'Ziguinchor, Sénégal',
                'password' => bcrypt('password123'),
                'code' => 'JKL012',
            ],
            [
                'id' => '550e8400-e29b-41d4-a716-446655440005',
                'titulaire' => 'Cheikh Sy',
                'nci' => '1237567890123',
                'email' => 'cheikh.sy@example.com',
                'telephone' => '+221757890123',
                'adresse' => 'Kaolack, Sénégal',
                'password' => bcrypt('password123'),
                'code' => 'MNO345',
            ],
        ];

        // Insérer les clients de test
        foreach ($clients as $client) {
            Client::firstOrCreate([
                'email' => $client['email']
            ], $client);
        }

        // Créer 15 clients supplémentaires avec la factory
        Client::factory()->count(15)->create();

        $this->command->info('Création de ' . (count($clients) + 15) . ' clients terminée.');
    }
}
