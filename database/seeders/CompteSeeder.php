<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Compte;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CompteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Récupérer tous les clients existants
        $clients = Client::all();

        if ($clients->isEmpty()) {
            $this->command->error('Aucun client trouvé. Veuillez exécuter ClientSeeder d\'abord.');
            return;
        }

        // Créer des comptes de test pour les premiers clients
        $comptesData = [
            [
                'id' => '550e8400-e29b-41d4-a716-446655440010',
                'numero_compte' => 'C00123456',
                'client_id' => $clients->first()->id,
                'type' => 'epargne',
                'solde' => 1250000.00,
                'devise' => 'FCFA',
                'statut' => 'bloque',
                'motif_blocage' => 'Inactivité de 30+ jours',
                'date_debut_blocage' => now()->subDays(45),
                'date_fin_blocage' => now()->addMonths(3),
                'version' => 2,
                'derniere_modification' => now()->subDays(10),
            ],
            [
                'id' => '550e8400-e29b-41d4-a716-446655440011',
                'numero_compte' => 'C00123457',
                'client_id' => $clients->first()->id,
                'type' => 'cheque',
                'solde' => 500000.00,
                'devise' => 'FCFA',
                'statut' => 'actif',
                'version' => 1,
                'derniere_modification' => now()->subDays(2),
            ],
            [
                'id' => '550e8400-e29b-41d4-a716-446655440012',
                'numero_compte' => 'C00123458',
                'client_id' => $clients->skip(1)->first()?->id ?? $clients->first()->id,
                'type' => 'epargne',
                'solde' => 2500000.00,
                'devise' => 'FCFA',
                'statut' => 'actif',
                'version' => 1,
                'derniere_modification' => now()->subHours(5),
            ],
            [
                'id' => '550e8400-e29b-41d4-a716-446655440013',
                'numero_compte' => 'C00123459',
                'client_id' => $clients->skip(2)->first()?->id ?? $clients->first()->id,
                'type' => 'cheque',
                'solde' => 750000.00,
                'devise' => 'FCFA',
                'statut' => 'actif',
                'version' => 1,
                'derniere_modification' => now()->subDays(1),
            ],
            [
                'id' => '550e8400-e29b-41d4-a716-446655440014',
                'numero_compte' => 'C00123460',
                'client_id' => $clients->skip(3)->first()?->id ?? $clients->first()->id,
                'type' => 'epargne',
                'solde' => 0.00,
                'devise' => 'FCFA',
                'statut' => 'ferme',
                'version' => 3,
                'derniere_modification' => now()->subMonths(1),
            ],
        ];

        // Insérer les comptes de test
        foreach ($comptesData as $compteData) {
            Compte::firstOrCreate([
                'numero_compte' => $compteData['numero_compte']
            ], $compteData);
        }

        // Créer des comptes supplémentaires avec la factory
        // Distribuer les comptes entre tous les clients
        $nombreComptesSupplementaires = max(0, 50 - count($comptesData));

        if ($nombreComptesSupplementaires > 0) {
            foreach ($clients as $client) {
                $comptesPourCeClient = rand(1, 3); // 1 à 3 comptes par client

                for ($i = 0; $i < $comptesPourCeClient && $nombreComptesSupplementaires > 0; $i++) {
                    Compte::factory()->pourClient($client)->create();
                    $nombreComptesSupplementaires--;
                }

                if ($nombreComptesSupplementaires <= 0) break;
            }
        }

        // Créer quelques comptes bloqués et fermés pour les tests
        Compte::factory()->bloque()->count(5)->create();
        Compte::factory()->ferme()->count(3)->create();

        $totalComptes = Compte::count();
        $this->command->info("Création de {$totalComptes} comptes terminée.");

        // Statistiques
        $stats = [
            'actifs' => Compte::actifs()->count(),
            'bloques' => Compte::bloques()->count(),
            'fermes' => Compte::where('statut', 'ferme')->count(),
            'epargne' => Compte::epargne()->count(),
            'cheque' => Compte::cheque()->count(),
        ];

        $this->command->info('Statistiques des comptes créés :');
        foreach ($stats as $type => $count) {
            $this->command->info("  - {$type}: {$count}");
        }
    }
}
