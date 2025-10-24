# Guide Complet du Projet API Banque - Laravel 10


## 1. Backlog du Projet

Le backlog représente les exigences fonctionnelles de l'API bancaire.

### Modélisation des Ressources

#### Ressource Compte
```json
{
  "id": "string",
  "numeroCompte": "string",
  "type": "epargne | cheque",
  "solde": "number",
  "devise": "string",
  "dateCreation": "date",
  "statut": "actif | bloque | ferme",
  "metadata": {
    "derniereModification": "datetime",
    "version": "number"
  }
}
```

#### Ressource Transactions
```json
{
  "id": "string",
  "compteId": "string",
  "type": "depot | retrait | virement | frais",
  "montant": "number",
  "devise": "string",
  "description": "string",
  "dateTransaction": "datetime",
  "statut": "en_attente | validee | annulee"
}
```

## 2. Endpoints API

Base URL : `http://api.moustapha.seck.com/api/v1`

### Lister tous les comptes
- **GET /api/v1/comptes**
- **Description :** Admin peut récupérer la liste de tous les comptes, Client peut récupérer la liste de ses comptes
- **Query Parameters :**
  - `page` : Numéro de page (default: 1)
  - `limit` : Nombre d'éléments par page (default: 10, max: 100)
  - `type` : Filtrer par type (epargne, cheque)
  - `statut` : Filtrer par statut (actif, bloque, ferme)
  - `search` : Recherche par titulaire ou numéro
  - `sort` : Tri (dateCreation, solde, titulaire)
  - `order` : Ordre (asc, desc)

**Response (200 OK) :**
```json
{
  "success": true,
  "data": [
    {
      "id": "550e8400-e29b-41d4-a716-446655440000",
      "numeroCompte": "C00123456",
      "titulaire": "Amadou Diallo",
      "type": "epargne",
      "solde": 1250000,
      "devise": "FCFA",
      "dateCreation": "2023-03-15T00:00:00Z",
      "statut": "bloque",
      "motifBlocage": "Inactivité de 30+ jours",
      "metadata": {
        "derniereModification": "2023-06-10T14:30:00Z",
        "version": 1
      }
    }
  ],
  "pagination": {
    "currentPage": 1,
    "totalPages": 3,
    "totalItems": 25,
    "itemsPerPage": 10,
    "hasNext": true,
    "hasPrevious": false
  },
  "links": {
    "self": "/api/v1/comptes?page=1&limit=10",
    "next": "/api/v1/comptes?page=2&limit=10",
    "first": "/api/v1/comptes?page=1&limit=10",
    "last": "/api/v1/comptes?page=3&limit=10"
  }
}
```

### Récupérer un compte spécifique
- **GET /api/v1/comptes/{compteId}**
- **Description :** Admin peut récupérer un compte par ID, Client peut récupérer un de ses comptes par ID

**Response (200 OK) :**
```json
{
  "success": true,
  "data": {
    "id": "550e8400-e29b-41d4-a716-446655440000",
    "numeroCompte": "C00123456",
    "titulaire": "Amadou Diallo",
    "type": "epargne",
    "solde": 1250000,
    "devise": "FCFA",
    "dateCreation": "2023-03-15T00:00:00Z",
    "statut": "bloque",
    "motifBlocage": "Inactivité de 30+ jours",
    "metadata": {
      "derniereModification": "2023-06-10T14:30:00Z",
      "version": 1
    }
  }
}
```

**Response (404 Not Found) :**
```json
{
  "success": false,
  "error": {
    "code": "COMPTE_NOT_FOUND",
    "message": "Le compte avec l'ID spécifié n'existe pas",
    "details": {
      "compteId": "550e8400-e29b-41d4-a716-446655440000"
    }
  }
}
```

### Créer un nouveau compte
- **POST /api/v1/comptes**
- **Description :** Créer un nouveau compte bancaire avec génération automatique de numéro et client

**Request Body :**
```json
{
  "type": "cheque",
  "soldeInitial": 500000,
  "devise": "FCFA",
  "client": {
    "id": null,
    "titulaire": "Hawa BB Wane",
    "nci": "",
    "email": "cheikh.sy@example.com",
    "telephone": "+221771234567",
    "adresse": "Dakar, Sénégal"
  }
}
```

**Règles de Validation :**
- Tous les champs sont obligatoires
- Téléphone est unique et respecte les critères d'un téléphone portable Sénégalais
- Solde à la création est supérieur ou égal à 10000
- L'email est unique
- Le numéro de téléphone est unique et respecte les règles d'un CNI Sénégalais

**Actions lors de la création :**
1. Vérifier l'existence du client
2. Si le client n'existe pas, le créer en générant :
   - Un mot de passe
   - Un code
3. Créer le compte en lui générant un numéro de compte
4. Envoyer un mail d'authentification avec le mot de passe
5. Envoyer par SMS le code
6. **NB :** Ce code devra être saisi après chaque connexion du client

**Response (201 Created) :**
```json
{
  "success": true,
  "message": "Compte créé avec succès",
  "data": {
    "id": "660f9511-f30c-52e5-b827-557766551111",
    "numeroCompte": "C00123460",
    "titulaire": "Cheikh Sy",
    "type": "cheque",
    "solde": 500000,
    "devise": "FCFA",
    "dateCreation": "2025-10-19T10:30:00Z",
    "statut": "actif",
    "metadata": {
      "derniereModification": "2025-10-19T10:30:00Z",
      "version": 1
    }
  }
}
```

**Response (400 Bad Request) :**
```json
{
  "success": false,
  "error": {
    "code": "VALIDATION_ERROR",
    "message": "Les données fournies sont invalides",
    "details": {
      "titulaire": "Le nom du titulaire est requis",
      "soldeInitial": "Le solde initial doit être supérieur à 0"
    }
  }
}
```

### Mettre à jour les informations du client
- **PATCH /api/v1/comptes/{compteId}**
- **Description :** Mettre à jour les informations du client associé au compte

**Request Body :**
```json
{
  "titulaire": "Amadou Diallo Junior",
  "informationsClient": {
    "telephone": "+221771234568",
    "email": "",
    "password": "",
    "nci": ""
  }
}
```

**Règles de Validation :**
- Tous les champs sont optionnels, mais au moins un champ de modification est requis
- Téléphone est unique et respecte les critères d'un téléphone portable Sénégalais
- L'email est unique
- Le mot de passe doit avoir au moins 10 caractères, commencer par une lettre majuscule, contenir au moins 2 lettres minuscules et 2 caractères spéciaux

### Supprimer un compte
- **DELETE /api/v1/comptes/{compteId}**
- **Description :** Supprimer un compte (soft delete)

**Response (200 OK) :**
```json
{
  "success": true,
  "message": "Compte supprimé avec succès",
  "data": {
    "id": "550e8400-e29b-41d4-a716-446655440000",
    "numeroCompte": "C00123456",
    "statut": "ferme",
    "dateFermeture": "2025-10-19T11:15:00Z"
  }
}
```

### Bloquer/Débloquer un compte
- **POST /api/v1/comptes/{compteId}/bloquer**
- **POST /api/v1/comptes/{compteId}/debloquer**

**Contraintes :**
1. On bloque un compte Épargne actif. Lors du blocage, on indique la date de début de blocage et le nombre de mois de blocage
2. On débloque un compte bloqué lorsque :
   - Le client en fait la demande
   - Lorsqu'on arrive à la date de déblocage

**Request Body (Blocage) :**
```json
{
  "motif": "Activité suspecte détectée",
  "duree": 30,
  "unite": "mois"
}
```

**Response (Blocage) :**
```json
{
  "success": true,
  "message": "Compte bloqué avec succès",
  "data": {
    "id": "550e8400-e29b-41d4-a716-446655440000",
    "statut": "bloque",
    "motifBlocage": "Activité suspecte détectée",
    "dateBlocage": "2025-10-19T11:20:00Z",
    "dateDeblocagePrevue": "2025-11-18T11:20:00Z"
  }
}
```

**Request Body (Déblocage) :**
```json
{
  "motif": "Vérification complétée"
}
```

**Response (Déblocage) :**
```json
{
  "success": true,
  "message": "Compte débloqué avec succès",
  "data": {
    "id": "550e8400-e29b-41d4-a716-446655440000",
    "statut": "actif",
    "dateDeblocage": "2025-10-19T12:00:00Z"
  }
}
```

### Authentification-Autorisation
- **POST /api/v1/auth/login** → Retourne access + refresh token
- **POST /api/v1/auth/refresh** → Renouvelle l'access token
- **POST /api/v1/auth/logout** → Invalide les tokens

**Spécification JWT :**
- Protocole : OAuth 2.0
- Access token : expire en 1h
- Refresh token : expire en 30 jours
- Algorithme : RS256 (clés asymétriques)

**Rate Limiting :**
- 1000 requêtes par heure par utilisateur
- 100 requêtes par minute par IP

**CORS :**
- Access-Control-Allow-Origin: http://front.moustapha.seck.com
- Access-Control-Allow-Methods: GET, POST, PATCH, DELETE
- Access-Control-Allow-Headers: Authorization, Content-Type

## 3. To Do - Liste des Tâches

### US 1.1 : Configuration Initiale du Projet Laravel
**Description :** Initialiser le projet Laravel avec la stack technique

**Étapes :**
- [x] Installer Laravel
- [x] Configurer le fichier .env (DB, APP_URL, APP_KEY)
- [x] Configurer la base de données (PostgreSQL)
- [x] Installer Laravel Passport pour l'API
- [x] Installer Laravel Debugbar : `composer require barryvdh/laravel-debugbar --dev`
- [x] Configurer Git et créer .gitignore
- [x] Créer les branches (dev/v1.0.0, production)
- [x] Configurer CORS dans config/cors.php

### US 1.2 : Modèle Eloquent - Client
**Description :** Créer le modèle Eloquent client avec migration et factory

**Étapes :**
- [ ] Modifier la migration Client
- [ ] Créer migration, factory, seeder
- [ ] Configurer les UUID comme clé primaire
- [ ] Définir la relation avec Client
- [ ] Ajouter les validations dans une Request personnalisée
- [ ] Créer le Factory pour les tests
- [ ] Créer le Seeder avec données de test
- [ ] Ajouter les index de base de données

### US 1.3 : Modèle Eloquent - Compte
**Description :** Créer le modèle Eloquent Compte avec migration et factory

**Étapes :**
- [ ] Créer le modèle, migration, factory, seeder
- [ ] Configurer les UUID comme clé primaire
- [ ] Générer automatiquement le numéro à l'aide d'un Mutator
- [ ] Ajouter les validations dans une Request personnalisée
- [ ] Ajouter la Relation client
- [ ] Créer le Factory pour les tests
- [ ] Créer le Seeder avec données de test
- [ ] Ajouter les index de base de données

### US 2.0 : Lister tous les comptes
**Description :** Admin peut récupérer la liste de tous les comptes, Client peut récupérer la liste de ses comptes

**Réalisation :**
- [ ] Créer un Controller pour récupérer la request et retourner la response
- [ ] Créer la structure des routes dans routes/api.php
- [ ] Grouper par version (v1)
- [ ] Créer des Resources pour les données retournées par la Response
- [ ] Configurer le CORS
- [ ] Définir un Trait Global pour la définition du format de la Response
- [ ] Définir les Exceptions Personnalisées
- [ ] Documenter chaque route
- [ ] Appliquer la Sécurité
- [ ] Appliquer le RateLimit

### Créer un nouveau compte
**Réalisation :**
- [ ] Créer un Controller pour récupérer la request et retourner la response
- [ ] Créer la structure des routes dans routes/api.php
- [ ] Grouper par version (v1)
- [ ] Créer une Règle qui valide le CNI
- [ ] Créer une Règle qui valide le Téléphone
- [ ] Créer des Request Personnalisées (Ajouter les règles de validation, Personnaliser les messages d'erreur)
- [ ] Documenter chaque route
- [ ] Appliquer la Sécurité

### Récupérer un compte spécifique
**Réalisation :**
- [ ] Créer la structure des routes dans routes/api.php
- [ ] Grouper par version (v1)
- [ ] Route Model Binding et Validation
- [ ] Créer des Resources pour les données retournées par la Response
- [ ] Créer un Controller pour récupérer la request et retourner la response
- [ ] Définir les Exceptions Personnalisées
- [ ] Documenter chaque route
- [ ] Appliquer la Sécurité

### Mettre à jour les informations du client
**Réalisation :**
- [ ] Créer un Controller pour récupérer la request et retourner la response
- [ ] Créer la structure des routes dans routes/api.php
- [ ] Grouper par version (v1)
- [ ] Créer des Request Personnalisées (Ajouter les règles de validation, Personnaliser les messages d'erreur)
- [ ] Documenter chaque route
- [ ] Appliquer la Sécurité

## 4. Architecture des Modèles Utilisateur - HÉRITAGE PROFESSIONNEL

### Héritage des Modèles Client et Admin

Les modèles **Client** et **Admin** doivent hériter du modèle **User** de Laravel qui doit etre adapter selon le travail pour une architecture propre et maintenable.

#### Modèle User (Classe Parent)
- **Attributs de base :**
  - `id` (clé primaire auto-incrémentée)
  - `name` (string)
  - `email` (string, unique)
  - `password` (hashed)
  - `email_verified_at` (datetime, nullable)
  - `remember_token` (string, nullable)
  - `created_at`, `updated_at` (timestamps)

- **Traits utilisés :**
  - `HasApiTokens` (Laravel Sanctum)
  - `HasFactory`
  - `Notifiable`

- **Rôles :** Stockés dans les claims JWT (client/admin)

#### Modèle Client (hérite de User)
- **Héritage :** `class Client extends User`
- **Attributs supplémentaires :**
  - `id` (UUID comme clé primaire - différent de User)
  - `titulaire` (string)
  - `nci` (string, unique, nullable)
  - `telephone` (string, unique)
  - `adresse` (string, nullable)
  - `code` (string, nullable - généré à la création)

- **Relations :**
  - `hasMany` Comptes

- **Migration séparée :** `create_clients_table` avec UUID

#### Modèle Admin (hérite de User)
- **Héritage :** `class Admin extends User`
- **Attributs :** Hérite tous les champs de User
- **Permissions :** Peut récupérer la liste de tous les comptes
- **Migration :** Utilise la table `users` de base avec un champ `role`

#### Avantages de l'Héritage
1. **Centralisation de l'authentification** : Un seul système pour tous les types d'utilisateurs
2. **Réutilisabilité du code** : Pas de duplication des méthodes d'auth
3. **Gestion des rôles simplifiée** : Via middleware et claims JWT
4. **Maintenance facilitée** : Changements dans User impactent tous les types d'utilisateurs

#### Migration Client
```php
Schema::create('clients', function (Blueprint $table) {
    $table->uuid('id')->primary(); // UUID différent de users.id
    $table->string('titulaire');
    $table->string('nci')->unique()->nullable();
    $table->string('email')->unique();
    $table->string('telephone')->unique();
    $table->string('adresse')->nullable();
    $table->string('password')->nullable();
    $table->string('code')->nullable();
    $table->timestamps();
    $table->index(['email', 'telephone']);
});
```

#### Implémentation dans le Modèle
```php
<?php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class Client extends Authenticatable // Hérite d'Authenticatable (User)
{
    use HasApiTokens;

    protected $fillable = [
        'id', 'titulaire', 'nci', 'email', 'telephone',
        'adresse', 'password', 'code'
    ];

    protected $keyType = 'string';
    public $incrementing = false;

    // Relations
    public function comptes()
    {
        return $this->hasMany(Compte::class);
    }
}
```

## 5. Règles Métier Importantes

### Règles Clients
- On doit avoir une migration client et une migration admin
- L'admin peut récupérer la liste de tous les comptes
- Le client peut récupérer la liste de ses comptes
- Récupérer les comptes chèque ou épargne actif

### Filtres et Scopes
- Pas de règles de filtre sur le controller (scope faire le filtre dans le model)
- Scope global si c'est sur toutes les ressources
- Scope local sur une seule ressource (model)
- NB : Pas de règle de filtre dans les contrôleurs, on doit les mettre dans chaque modèle correspondant scope

### Rate Limiting et Logging
- Logger ceux qui font plus de 10 requêtes par jour
- On doit créer un middleware pour les utilisateurs rate limit
- L'utilisateur doit faire 10 requêtes par jour
- Quand l'utilisateur atteint le nombre de requêtes limites, prendre ses infos et les stocker quelque part

### Blocage de Comptes
- On peut bloquer un compte : un compte épargne actif
- L'utilisateur donne la date de début de blocage et le nombre de jours
- Calculer la date de fin de blocage selon la date de début
- Quand on bloque le compte ou la date de début de blocage commence, on le met dans la base Neon avec ses transactions via un job
- Un autre job vérifie si la date de blocage est terminée, si oui récupère les données de la base Neon et les ramène dans la base local
- Les comptes peuvent être dans Neon pour affichage, on vérifie dans local d'abord
- La recherche route model binding pas en local, on le cherche sur Neon

### Validation Personnalisée
- Créer des règles personnalisées pour le téléphone et le CNI, pas de regex
- Règles de validation qui valident les téléphones et cartes d'identité sénégalais

### Soft Delete
- Utiliser soft delete partout

### Exceptions
- Ils font qu'on définisse les exceptions personnalisées dans nos try catch
- Le contrôleur ne fera que lever l'exception
- Les contrôleurs ne doivent pas dépasser 10 lignes parce qu'ils gèrent que request et response, ce n'est pas à lui de gérer les filtres de requêtes, etc.

### Observers et Events
- On crée un observateur pour éviter de mettre certains traitements dans les contrôleurs
- L'observateur va envoyer par SMS après la création à travers un listener "send notification" le code et le mail
- On utilise la dépendance mail et Twilio, pas de couplage fort

### Authentification
- On utilise la stratégie OAuth 2 et Passport
- Les claims stockent les rôles
- Le token est stocké dans les cookies
- Un middleware role qui récupère les permissions rôle de l'utilisateur connecté

### Routes
- Il faut grouper les routes suivant les versions

### Transactions
- En créant les comptes, on crée en même temps les transactions (seeders dans compte et seeders dans transaction)
- Il n'y aura pas d'endpoint pour la ressource transaction
- On aura juste des données de transaction qu'on va exploiter via les comptes

### Code et Solde
- Le code devra être saisi lors de la première connexion
- Le solde est égal dépôt - retrait

## 5. Gestion des Branches Git - GUIDE DÉTAILLÉ

### ⚠️ IMPORTANT : Comprendre Git Flow

La gestion des branches est CRUCIALE dans ce projet. 

### Structure des Branches

```
main/production (branche principale de production)
├── dev (branche de développement)
│   ├── dev/v1.0.0 (liste des comptes)
│   ├── dev/v1.1.0 (création de comptes)
│   ├── dev/v1.2.0 (récupération compte spécifique)
│   ├── dev/v1.3.0 (mise à jour compte)
│   └── dev/v1.4.0 (suppression compte)
```

### Workflow Complet

#### 1. Initialisation du Projet

```bash
# Créer le repo Git
git init
git add .
git commit -m "Initial commit - Configuration Laravel"

# Créer la branche principale de production
git branch main
git checkout main

# Créer la branche dev
git checkout -b dev
git push -u origin dev
```

#### 2. Créer une Nouvelle Fonctionnalité (ex: v1.0.0 - Liste des comptes)

```bash
# Depuis la branche dev
git checkout dev
git pull origin dev

# Créer la branche de fonctionnalité
git checkout -b dev/v1.0.0

# Travailler sur la fonctionnalité
# Commits réguliers avec des messages descriptifs
git add .
git commit -m "feat: créer modèle Compte avec migration"
git add .
git commit -m "feat: implémenter endpoint GET /comptes"
git add .
git commit -m "feat: ajouter filtres et pagination"
git add .
git commit -m "test: ajouter tests pour l'endpoint comptes"

# Pousser la branche
git push -u origin dev/v1.0.0
```

#### 3. Quand la Fonctionnalité est Terminée

```bash
# Merge dans dev
git checkout dev
git pull origin dev
git merge dev/v1.0.0

# Résoudre les conflits si nécessaire
# Tester que tout fonctionne
git push origin dev

# Créer un tag pour la version
git tag v1.0.0
git push --tags
```

#### 4. Déploiement en Production

```bash
# Merge dans main/production UNIQUEMENT quand tout est testé
git checkout main
git pull origin main
git merge dev

# Déployer
git push origin main

# Tag de production
git tag production-v1.0.0
git push --tags
```

#### 5. Créer la Fonctionnalité Suivante

```bash
# Depuis dev
git checkout dev
git pull origin dev

# Créer la nouvelle branche
git checkout -b dev/v1.1.0

# Répéter le processus...
```

### Règles d'Or pour les Branches

1. **NE JAMAIS pousser directement sur main/production**
2. **Toujours créer des Pull Requests (PR) pour review**
3. **Les branches dev contiennent les versions (v1.0.0, v1.1.0, etc.)**
4. **Merge seulement quand la fonctionnalité est complète et testée**
5. **Commits fréquents avec messages descriptifs**
6. **Tester avant chaque merge**

### Commandes Essentielles

```bash
# Voir toutes les branches
git branch -a

# Changer de branche
git checkout nom-branche

# Créer et changer de branche
git checkout -b nouvelle-branche

# Voir l'état
git status

# Commits
git add .
git commit -m "message descriptif"

# Push
git push origin nom-branche

# Pull
git pull origin nom-branche

# Merge
git checkout branche-cible
git merge branche-source

# Tags
git tag nom-tag
git push --tags
```

### Quand Merge dans Production ?

- ✅ Fonctionnalité complètement développée
- ✅ Tests unitaires et fonctionnels passés
- ✅ Code review effectué
- ✅ Documentation mise à jour
- ✅ Aucun bug connu
- ✅ Toutes les migrations exécutées
- ✅ Seeders testés

## 6. Déploiement Swagger

### Étapes à suivre :
1. Installer Swagger 8 pour Laravel 10 ou 11 via la commande :
   ```bash
   composer require "darkaonline/l5-swagger:8^0"
   ```

2. Générer le fichier initial :
   ```bash
   php artisan l5-swagger:generate
   ```

3. Publier les fichiers de config :
   ```bash
   php artisan vendor:publish --provider "L5Swagger\L5SwaggerServiceProvider"
   ```

Ces étapes vous les faites sur votre projet à documenter.

C'est pour le déploiement : vous copiez le fichier JSON généré par Swagger sur votre app et vous le remplacez par celui dans mon dossier public et déployez sur Vercel.

Le fichier à remplacer se trouve dans public.

Lors de votre déploiement, une fois dans la config Vercel, il faut indiquer le dossier public pour ne pas avoir le 404 not found.

## 7. Guide Laravel pour Débutants

### Concepts Clés

#### 1. Structure MVC
- **Models** : Représentent les données (app/Models/)
- **Views** : Interfaces utilisateur (resources/views/) - moins utilisé en API
- **Controllers** : Logique métier (app/Http/Controllers/)

#### 2. Migrations
- Définition de la structure de la base de données
- `php artisan make:migration nom_migration`
- `php artisan migrate` pour exécuter

#### 3. Eloquent ORM
- Interaction avec la base de données
- Relations : hasOne, hasMany, belongsTo, belongsToMany
- Scopes pour filtres personnalisés

#### 4. Routes
- Définition dans routes/api.php pour les API
- Groupement par versions : `Route::prefix('v1')->group(...)`
- Middleware pour authentification et autorisation

#### 5. Middleware
- Filtre les requêtes HTTP
- Authentification, CORS, rate limiting
- `php artisan make:middleware NomMiddleware`

#### 6. Requests Personnalisées
- Validation des données d'entrée
- `php artisan make:request NomRequest`

#### 7. Resources
- Formatage des réponses JSON
- `php artisan make:resource NomResource`

#### 8. Jobs
- Tâches asynchrones
- `php artisan make:job NomJob`

#### 9. Observers
- Écoute des événements sur les modèles
- `php artisan make:observer NomObserver`

### Bonnes Pratiques

1. **Controllers légers** : < 10 lignes, seulement request/response
2. **Logique métier dans les modèles/services**
3. **Validation dans les Request classes**
4. **Utiliser les scopes pour les filtres**
5. **Soft delete pour suppression logique**
6. **UUID pour les clés primaires**
7. **API versioning**
8. **Documentation Swagger**

### Optimisation et Cache - API RESTful Performante

#### Cache pour Éviter les Requêtes Inutiles

L'API doit être RESTful et performante. Pour éviter d'interroger la base de données inutilement, on doit implémenter un système de cache :

##### Redis comme Base de Données Tampon

- **Installer Redis** :
  ```bash
  composer require predis/predis
  ```

- **Configuration** dans `.env` :
  ```env
  CACHE_DRIVER=redis
  REDIS_HOST=127.0.0.1
  REDIS_PASSWORD=null
  REDIS_PORT=6379
  ```

##### Stratégies de Cache

1. **Cache des Requêtes Fréquentes** :
   ```php
   // Dans le Controller
   public function index(Request $request)
   {
       $cacheKey = 'comptes_' . md5(serialize($request->all()));

       return Cache::remember($cacheKey, 3600, function () use ($request) {
           // Logique de récupération des comptes
           return Compte::withFilters($request)->paginate();
       });
   }
   ```

2. **Cache des Comptes Individuels** :
   ```php
   public function show($id)
   {
       return Cache::remember("compte_{$id}", 1800, function () use ($id) {
           return Compte::findOrFail($id);
       });
   }
   ```

3. **Invalidation du Cache** :
   ```php
   // Dans les Observers ou Events
   public function updated(Compte $compte)
   {
       Cache::forget("compte_{$compte->id}");
       Cache::flush(); // Ou stratégie plus fine
   }
   ```

##### Headers HTTP pour Cache

- **ETag** pour validation conditionnelle
- **Last-Modified** pour contrôle de fraîcheur
- **Cache-Control** pour directives de cache

```php
// Dans le Controller
public function show(Compte $compte)
{
    $response = response()->json($compte);

    // Ajouter ETag
    $etag = md5($compte->updated_at . $compte->id);
    $response->setEtag($etag);

    // Cache-Control
    $response->header('Cache-Control', 'public, max-age=3600');

    return $response;
}
```

##### Cache des Tokens JWT

- Stocker temporairement les tokens validés
- Éviter la vérification répétée des tokens
- Utiliser Redis pour session store

##### Cache des Métadonnées

- Informations rarement modifiées (devises, types de compte)
- Cache permanent avec invalidation manuelle

#### Avantages du Cache

1. **Performance** : Réduction drastique des temps de réponse
2. **Charge DB** : Moins de requêtes vers PostgreSQL/Neon
3. **Scalabilité** : Support de plus d'utilisateurs simultanés
4. **RESTful** : Respect des principes de cache HTTP

#### Outils de Monitoring

- **Laravel Telescope** pour monitoring des performances
- **Cache hit/miss ratios**
- Métriques Redis (memory usage, connections)

### Commandes Essentielles Laravel

```bash
# Créer des éléments
php artisan make:model NomModel
php artisan make:controller NomController
php artisan make:migration nom_migration
php artisan make:request NomRequest
php artisan make:resource NomResource
php artisan make:job NomJob
php artisan make:observer NomObserver --model=NomModel

# Base de données
php artisan migrate
php artisan migrate:rollback
php artisan migrate:status
php artisan db:seed

# Cache et optimisation
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Tests
php artisan test
```

### Debugging
- Utiliser `dd()` ou `dump()` pour déboguer
- Laravel Debugbar pour développement
- Logs dans storage/logs/

---

