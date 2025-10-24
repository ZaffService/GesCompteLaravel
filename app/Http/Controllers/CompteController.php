<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Compte;
use App\Http\Requests\StoreCompteRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class CompteController extends Controller
{
    /**
     * Lister tous les comptes avec filtres et pagination
     */
    public function index(Request $request): JsonResponse
    {
        // Validation des paramètres
        $request->validate([
            'page' => 'nullable|integer|min:1',
            'limit' => 'nullable|integer|min:1|max:100',
            'type' => 'nullable|string|in:epargne,cheque',
            'statut' => 'nullable|string|in:actif,bloque,ferme',
            'search' => 'nullable|string|max:255',
            'sort' => 'nullable|string|in:dateCreation,solde,titulaire',
            'order' => 'nullable|string|in:asc,desc',
        ]);

        // Clé de cache pour éviter les requêtes répétées
        $cacheKey = 'comptes_' . md5(serialize($request->all()));

        $comptes = Cache::remember($cacheKey, 3600, function () use ($request) {
            $query = Compte::with('client:id,titulaire');

            // Filtres selon le rôle de l'utilisateur
            $user = auth()->user();

            if ($user instanceof Client) {
                // Client ne voit que ses propres comptes
                $query->where('client_id', $user->id);
            }
            // Admin voit tous les comptes (pas de filtre supplémentaire)

            // Appliquer les filtres de requête
            if ($request->has('type') && $request->type) {
                $query->parType($request->type);
            }

            if ($request->has('statut') && $request->statut) {
                $query->parStatut($request->statut);
            }

            if ($request->has('search') && $request->search) {
                $query->recherche($request->search);
            }

            // Tri
            $sort = $request->get('sort', 'dateCreation');
            $order = $request->get('order', 'desc');
            $query->trierPar($sort, $order);

            // Pagination
            $limit = $request->get('limit', 10);
            return $query->paginate($limit);
        });

        // Formater la réponse avec les métadonnées
        $response = $this->formatPaginatedResponse($comptes);

        return response()->json($response);
    }

    /**
     * Afficher un compte spécifique
     */
    public function show(Compte $compte): JsonResponse
    {
        // Vérifier les permissions
        $user = auth()->user();

        if ($user instanceof Client && $compte->client_id !== $user->id) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'ACCESS_DENIED',
                    'message' => 'Vous n\'avez pas accès à ce compte'
                ]
            ], 403);
        }

        // Cache individuel du compte
        $cacheKey = "compte_{$compte->id}";
        $compteData = Cache::remember($cacheKey, 1800, function () use ($compte) {
            return $compte->load('client:id,titulaire');
        });

        return response()->json([
            'success' => true,
            'data' => $this->formatCompteData($compteData)
        ]);
    }

    /**
     * Créer un nouveau compte
     */
    public function store(StoreCompteRequest $request): JsonResponse
    {
        try {
            // Vérifier/créer le client
            $client = $this->getOrCreateClient($request->client);

            // Créer le compte
            $compte = new Compte();
            $compte->client_id = $client->id;
            $compte->type = $request->type;
            $compte->solde = $request->soldeInitial;
            $compte->devise = $request->devise;
            $compte->save();

            // Invalider le cache
            Cache::flush();

            return response()->json([
                'success' => true,
                'message' => 'Compte créé avec succès',
                'data' => $this->formatCompteData($compte->load('client:id,titulaire'))
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'CREATION_FAILED',
                    'message' => 'Erreur lors de la création du compte',
                    'details' => config('app.debug') ? $e->getMessage() : null
                ]
            ], 500);
        }
    }

    /**
     * Mettre à jour un compte
     */
    public function update(Request $request, Compte $compte): JsonResponse
    {
        // Validation basique (à étendre selon les besoins)
        $request->validate([
            'titulaire' => 'sometimes|string|max:255',
            'informationsClient' => 'sometimes|array',
            'informationsClient.telephone' => 'sometimes|string|regex:/^\+221(77|78|76|70|75|33|32)\d{7}$/',
            'informationsClient.email' => 'sometimes|email|unique:clients,email,' . $compte->client_id,
        ]);

        try {
            // Mise à jour du client si nécessaire
            if ($request->has('titulaire') || $request->has('informationsClient')) {
                $clientData = [];

                if ($request->has('titulaire')) {
                    $clientData['titulaire'] = $request->titulaire;
                }

                if ($request->has('informationsClient')) {
                    $clientData = array_merge($clientData, $request->informationsClient);
                }

                $compte->client->update($clientData);
            }

            // Invalider le cache
            Cache::forget("compte_{$compte->id}");
            Cache::flush();

            return response()->json([
                'success' => true,
                'message' => 'Compte mis à jour avec succès',
                'data' => $this->formatCompteData($compte->load('client:id,titulaire'))
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'UPDATE_FAILED',
                    'message' => 'Erreur lors de la mise à jour du compte'
                ]
            ], 500);
        }
    }

    /**
     * Supprimer un compte (soft delete)
     */
    public function destroy(Compte $compte): JsonResponse
    {
        try {
            $compte->delete(); // Soft delete

            // Invalider le cache
            Cache::forget("compte_{$compte->id}");
            Cache::flush();

            return response()->json([
                'success' => true,
                'message' => 'Compte supprimé avec succès',
                'data' => [
                    'id' => $compte->id,
                    'numeroCompte' => $compte->numero_compte,
                    'statut' => 'ferme',
                    'dateFermeture' => now()
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'DELETE_FAILED',
                    'message' => 'Erreur lors de la suppression du compte'
                ]
            ], 500);
        }
    }

    /**
     * Bloquer un compte
     */
    public function bloquer(Request $request, Compte $compte): JsonResponse
    {
        $request->validate([
            'motif' => 'required|string|max:500',
            'duree' => 'required|integer|min:1|max:365',
            'unite' => 'required|string|in:jours,mois'
        ]);

        if (!$compte->peutEtreBloque()) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'INVALID_OPERATION',
                    'message' => 'Ce compte ne peut pas être bloqué'
                ]
            ], 400);
        }

        $result = $compte->bloquer($request->motif, $request->duree, $request->unite);

        if (!$result) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'BLOCK_FAILED',
                    'message' => 'Échec du blocage du compte'
                ]
            ], 500);
        }

        // Invalider le cache
        Cache::forget("compte_{$compte->id}");
        Cache::flush();

        return response()->json([
            'success' => true,
            'message' => 'Compte bloqué avec succès',
            'data' => $this->formatCompteData($compte->load('client:id,titulaire'))
        ]);
    }

    /**
     * Débloquer un compte
     */
    public function debloquer(Request $request, Compte $compte): JsonResponse
    {
        $request->validate([
            'motif' => 'required|string|max:500'
        ]);

        if (!$compte->peutEtreDebloque()) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'INVALID_OPERATION',
                    'message' => 'Ce compte ne peut pas être débloqué'
                ]
            ], 400);
        }

        $result = $compte->debloquer($request->motif);

        if (!$result) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'UNBLOCK_FAILED',
                    'message' => 'Échec du déblocage du compte'
                ]
            ], 500);
        }

        // Invalider le cache
        Cache::forget("compte_{$compte->id}");
        Cache::flush();

        return response()->json([
            'success' => true,
            'message' => 'Compte débloqué avec succès',
            'data' => $this->formatCompteData($compte->load('client:id,titulaire'))
        ]);
    }

    /**
     * Récupérer ou créer un client
     */
    private function getOrCreateClient(array $clientData): Client
    {
        if (isset($clientData['id']) && $clientData['id']) {
            return Client::findOrFail($clientData['id']);
        }

        // Créer un nouveau client
        $client = new Client();
        $client->titulaire = $clientData['titulaire'];
        $client->email = $clientData['email'];
        $client->telephone = $clientData['telephone'];
        $client->adresse = $clientData['adresse'] ?? null;
        $client->nci = $clientData['nci'] ?? null;
        $client->password = bcrypt('password123'); // Mot de passe temporaire
        $client->code = Client::generateCode();
        $client->save();

        return $client;
    }

    /**
     * Formater les données d'un compte pour la réponse
     */
    private function formatCompteData(Compte $compte): array
    {
        return [
            'id' => $compte->id,
            'numeroCompte' => $compte->numero_compte,
            'titulaire' => $compte->client->titulaire,
            'type' => $compte->type,
            'solde' => $compte->solde,
            'devise' => $compte->devise,
            'dateCreation' => $compte->created_at,
            'statut' => $compte->statut,
            'motifBlocage' => $compte->motif_blocage,
            'dateDebutBlocage' => $compte->date_debut_blocage,
            'dateFinBlocage' => $compte->date_fin_blocage,
            'metadata' => [
                'derniereModification' => $compte->derniere_modification,
                'version' => $compte->version
            ]
        ];
    }

    /**
     * Formater la réponse paginée
     */
    private function formatPaginatedResponse($paginatedData): array
    {
        return [
            'success' => true,
            'data' => $paginatedData->getCollection()->map(function ($compte) {
                return $this->formatCompteData($compte);
            }),
            'pagination' => [
                'currentPage' => $paginatedData->currentPage(),
                'totalPages' => $paginatedData->lastPage(),
                'totalItems' => $paginatedData->total(),
                'itemsPerPage' => $paginatedData->perPage(),
                'hasNext' => $paginatedData->hasMorePages(),
                'hasPrevious' => $paginatedData->currentPage() > 1
            ],
            'links' => [
                'self' => $paginatedData->url($paginatedData->currentPage()),
                'first' => $paginatedData->url(1),
                'last' => $paginatedData->url($paginatedData->lastPage()),
                'next' => $paginatedData->nextPageUrl(),
                'prev' => $paginatedData->previousPageUrl()
            ]
        ];
    }
}
