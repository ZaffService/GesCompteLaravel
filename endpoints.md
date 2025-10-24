# 🧪 GUIDE COMPLET DE TEST API BANQUE - POSTMAN

5 clients de test avec codes :

amadou.diallo@example.com / password123 / Code: ABC123
fatou.sow@example.com / password123 / Code: DEF456
moussa.ndiaye@example.com / password123 / Code: GHI789
aissatou.ba@example.com / password123 / Code: JKL012
cheikh.sy@example.com / password123 / Code: MNO345


## 📋 Vue d'ensemble

Ce guide vous explique **ÉTAPE PAR ÉTAPE** comment tester l'API Banque avec Postman. Tous les endpoints sont implémentés et testables.

**Base URL :** `http://127.0.0.1:8000/api/v1`

## 🚀 ÉTAPE 1 : Préparation de l'environnement

### 1.1 Démarrer le serveur Laravel
Ouvrez un terminal et exécutez :
```bash
cd /chemin/vers/votre/projet/Laravel-10
php artisan serve
```
Le serveur démarre sur `http://127.0.0.1:8000`

### 1.2 Ouvrir Postman
- Lancez Postman
- Créez une nouvelle collection : **"API Banque Tests"**

### 1.3 Configurer les variables d'environnement
Dans Postman :
1. Cliquez sur **"Environments"** (environnements)
2. Créez un nouvel environnement : **"API Banque Dev"**
3. Ajoutez ces variables :
   - `base_url` = `http://127.0.0.1:8000/api/v1`
   - `token` = (laisser vide pour l'instant)

### 1.4 Headers par défaut
Pour chaque requête, ajoutez ces headers :
- `Accept`: `application/json`
- `Content-Type`: `application/json`

## 🔐 ÉTAPE 2 : Authentification

### 2.1 Créer la requête de connexion
Dans Postman :
1. **Nouvelle requête** dans votre collection
2. **Méthode** : `POST`
3. **URL** : `{{base_url}}/auth/login`
4. **Headers** : Ajoutez `Accept` et `Content-Type`
5. **Body** : `raw` → `JSON`
6. **Collez ce JSON** :
```json
{
  "email": "amadou.diallo@example.com",
  "password": "password123",
  "code": "ABC123"
}
```

### 2.2 Exécuter la connexion
1. Cliquez sur **"Send"**
2. Vous devriez recevoir :
```json
{
  "success": true,
  "message": "Connexion réussie",
  "data": {
    "user": {
      "id": "550e8400-e29b-41d4-a716-446655440001",
      "titulaire": "Amadou Diallo",
      "email": "amadou.diallo@example.com",
      "telephone": "+221771234567"
    },
    "access_token": "1|abc123def456...",
    "token_type": "Bearer",
    "expires_in": 3600
  }
}
```

### 2.3 Sauvegarder le token
1. Copiez le `access_token` de la réponse
2. Dans les variables d'environnement Postman :
   - Variable `token` = collez le token copié
3. **Toutes les futures requêtes** utiliseront `Authorization: Bearer {{token}}`

## 📊 ÉTAPE 3 : Tester les comptes

### 3.1 Lister tous les comptes
**Nouvelle requête :**
- **Méthode** : `GET`
- **URL** : `{{base_url}}/comptes`
- **Headers** : `Authorization: Bearer {{token}}`

**Cliquez Send** → Vous verrez tous les comptes (58 comptes)

### 3.2 Lister avec pagination
**Modifier l'URL :**
```
{{base_url}}/comptes?page=1&limit=5
```
→ Seulement 5 comptes par page

### 3.3 Filtrer par statut actif
**URL :**
```
{{base_url}}/comptes?statut=actif&limit=3
```
→ Comptes actifs uniquement

### 3.4 Filtrer par type épargne
**URL :**
```
{{base_url}}/comptes?type=epargne&limit=3
```
→ Comptes épargne uniquement

### 3.5 Recherche par nom
**URL :**
```
{{base_url}}/comptes?search=Diallo
```
→ Comptes du client "Diallo"

### 3.6 Tri par solde décroissant
**URL :**
```
{{base_url}}/comptes?sort=solde&order=desc&limit=5
```
→ Comptes triés par solde (plus riches d'abord)

## 🔍 ÉTAPE 4 : Détails d'un compte spécifique

### 4.1 Récupérer un compte par ID
**Nouvelle requête :**
- **Méthode** : `GET`
- **URL** : `{{base_url}}/comptes/550e8400-e29b-41d4-a716-446655440010`
- **Headers** : `Authorization: Bearer {{token}}`

**Note :** Utilisez un ID de compte que vous avez vu dans la liste précédente

## ➕ ÉTAPE 5 : Créer un nouveau compte

### 5.1 Créer un compte pour client existant
**Nouvelle requête :**
- **Méthode** : `POST`
- **URL** : `{{base_url}}/comptes`
- **Headers** : `Authorization: Bearer {{token}}`
- **Body** (JSON) :
```json
{
  "type": "cheque",
  "soldeInitial": 500000,
  "devise": "FCFA",
  "client": {
    "id": "550e8400-e29b-41d4-a716-446655440001"
  }
}
```

### 5.2 Créer un compte avec nouveau client
**Body alternatif :**
```json
{
  "type": "epargne",
  "soldeInitial": 1000000,
  "devise": "FCFA",
  "client": {
    "titulaire": "Marie Dupont",
    "email": "marie.dupont@example.com",
    "telephone": "+221771234568",
    "adresse": "Dakar, Sénégal"
  }
}
```

## ✏️ ÉTAPE 6 : Modifier un compte

### 6.1 Modifier les informations client
**Nouvelle requête :**
- **Méthode** : `PATCH`
- **URL** : `{{base_url}}/comptes/550e8400-e29b-41d4-a716-446655440010`
- **Headers** : `Authorization: Bearer {{token}}`
- **Body** :
```json
{
  "titulaire": "Amadou Diallo Junior",
  "informationsClient": {
    "telephone": "+221771234569",
    "email": "amadou.junior@example.com"
  }
}
```

## 🚫 ÉTAPE 7 : Bloquer un compte

### 7.1 Bloquer un compte épargne actif
**Nouvelle requête :**
- **Méthode** : `POST`
- **URL** : `{{base_url}}/comptes/{id-compte-actif-epargne}/bloquer`
- **Headers** : `Authorization: Bearer {{token}}`
- **Body** :
```json
{
  "motif": "Activité suspecte détectée",
  "duree": 30,
  "unite": "mois"
}
```

## ✅ ÉTAPE 8 : Débloquer un compte

### 8.1 Débloquer un compte bloqué
**Nouvelle requête :**
- **Méthode** : `POST`
- **URL** : `{{base_url}}/comptes/{id-compte-bloque}/debloquer`
- **Headers** : `Authorization: Bearer {{token}}`
- **Body** :
```json
{
  "motif": "Vérification complétée"
}
```

## 🗑️ ÉTAPE 9 : Supprimer un compte

### 9.1 Supprimer un compte (soft delete)
**Nouvelle requête :**
- **Méthode** : `DELETE`
- **URL** : `{{base_url}}/comptes/{id-compte-a-supprimer}`
- **Headers** : `Authorization: Bearer {{token}}`

## 🔄 ÉTAPE 10 : Rafraîchir le token

### 10.1 Rafraîchir avant expiration
**Nouvelle requête :**
- **Méthode** : `POST`
- **URL** : `{{base_url}}/auth/refresh`
- **Headers** : `Authorization: Bearer {{token}}`

## 🚪 ÉTAPE 11 : Déconnexion

### 11.1 Se déconnecter
**Nouvelle requête :**
- **Méthode** : `POST`
- **URL** : `{{base_url}}/auth/logout`
- **Headers** : `Authorization: Bearer {{token}}`

## 👥 ÉTAPE 12 : Tester avec différents utilisateurs

### 12.1 Se connecter avec un autre client
Répétez l'étape 2.1 avec ces identifiants :

**Client 2 :**
```json
{
  "email": "fatou.sow@example.com",
  "password": "password123",
  "code": "DEF456"
}
```

**Client 3 :**
```json
{
  "email": "moussa.ndiaye@example.com",
  "password": "password123",
  "code": "GHI789"
}
```

**Client 4 :**
```json
{
  "email": "aissatou.ba@example.com",
  "password": "password123",
  "code": "JKL012"
}
```

**Client 5 :**
```json
{
  "email": "cheikh.sy@example.com",
  "password": "password123",
  "code": "MNO345"
}
```

### 12.2 Vérifier l'isolation des données
- Connecté en Client 1 → voit seulement ses comptes
- Connecté en Client 2 → voit seulement ses comptes
- **Admin** pourrait voir tous les comptes (pas encore implémenté)

## ⚠️ ÉTAPE 13 : Tester les erreurs

### 13.1 Token invalide
**Supprimez ou modifiez le token** dans les variables d'environnement → Erreur 401

### 13.2 Accès non autorisé
**Essayez d'accéder au compte d'un autre client** → Erreur 403

### 13.3 Ressource non trouvée
**URL avec ID inexistant** → Erreur 404

### 13.4 Données invalides
**Body avec email déjà utilisé** → Erreur 422

### 13.5 Limite de taux dépassée
**Faites plus de 10 requêtes** dans la même journée → Erreur 429

## 📋 DONNÉES DE TEST DISPONIBLES

### Clients de test (5 premiers avec codes connus) :
1. **Amadou Diallo** - `amadou.diallo@example.com` - Code: `ABC123`
2. **Fatou Sow** - `fatou.sow@example.com` - Code: `DEF456`
3. **Moussa Ndiaye** - `moussa.ndiaye@example.com` - Code: `GHI789`
4. **Aïssatou Ba** - `aissatou.ba@example.com` - Code: `JKL012`
5. **Cheikh Sy** - `cheikh.sy@example.com` - Code: `MNO345`

### Statistiques des comptes :
- **Total** : 58 comptes
- **Actifs** : 18 comptes
- **Bloqués** : 21 comptes
- **Fermés** : 19 comptes
- **Épargne** : 31 comptes
- **Chèque** : 27 comptes

## 🎯 WORKFLOW COMPLET DE TEST

1. ✅ **Démarrer serveur** (`php artisan serve`)
2. ✅ **Créer collection Postman** avec variables
3. ✅ **Login** avec client 1
4. ✅ **Sauvegarder token**
5. ✅ **Lister comptes** (tous, filtrés, paginés)
6. ✅ **Voir détail** d'un compte
7. ✅ **Créer compte** (client existant + nouveau)
8. ✅ **Modifier compte**
9. ✅ **Bloquer compte** (épargne actif)
10. ✅ **Débloquer compte**
11. ✅ **Supprimer compte**
12. ✅ **Changer d'utilisateur** et vérifier isolation
13. ✅ **Tester erreurs** (401, 403, 404, 422, 429)
14. ✅ **Logout**

## 💡 ASTUCES POSTMAN

- **Sauvegardez les IDs** des comptes créés pour les tests suivants
- **Utilisez les variables** `{{base_url}}` et `{{token}}`
- **Testez les filtres** combinés : `?statut=actif&type=epargne&page=1`
- **Vérifiez toujours** la réponse avant de continuer
- **Les erreurs sont normales** - elles permettent de comprendre l'API

---

**🚀 Vous êtes maintenant prêt à tester l'API complète !** Commencez par l'étape 1 et suivez le guide pas à pas.

## 📚 Endpoints Disponibles

### 1. 🔓 Authentification (Routes publiques)

#### POST `/auth/login`
Connexion utilisateur avec vérification de code (première connexion).

**Headers :**
```
Accept: application/json
Content-Type: application/json
```

**Body (JSON) :**
```json
{
  "email": "client1@example.com",
  "password": "password123",
  "code": "ABC123"
}
```

**Exemple de test :**
```json
{
  "email": "client1@example.com",
  "password": "password123"
}
```

**Réponse de succès :**
```json
{
  "success": true,
  "message": "Connexion réussie",
  "data": {
    "user": {
      "id": "uuid",
      "titulaire": "Client Test 1",
      "email": "client1@example.com",
      "telephone": "+221771234567"
    },
    "access_token": "token_here",
    "token_type": "Bearer",
    "expires_in": 3600
  }
}
```

**⚠️ Note :** Pour la première connexion, le champ `code` est requis. Le code est généré automatiquement lors de la création du client.

#### POST `/auth/refresh`
Rafraîchir le token d'accès.

**Headers :**
```
Accept: application/json
Content-Type: application/json
Authorization: Bearer {token}
```

**Body :** Aucun

#### POST `/auth/logout`
Déconnexion et révocation du token.

**Headers :**
```
Accept: application/json
Content-Type: application/json
Authorization: Bearer {token}
```

### 2. 🔒 Comptes (Routes protégées)

#### GET `/comptes`
Lister tous les comptes avec filtres et pagination.

**Headers :**
```
Accept: application/json
Authorization: Bearer {token}
```

**Query Parameters (optionnels) :**
- `page`: numéro de page (défaut: 1)
- `limit`: éléments par page (défaut: 10, max: 100)
- `type`: `epargne` ou `cheque`
- `statut`: `actif`, `bloque`, ou `ferme`
- `search`: recherche par titulaire ou numéro
- `sort`: tri par `dateCreation`, `solde`, `titulaire`
- `order`: `asc` ou `desc`

**Exemples de test :**

1. **Tous les comptes actifs :**
   ```
   GET /api/v1/comptes?statut=actif&limit=5
   ```

2. **Comptes épargne triés par solde :**
   ```
   GET /api/v1/comptes?type=epargne&sort=solde&order=desc
   ```

3. **Recherche par titulaire :**
   ```
   GET /api/v1/comptes?search=Diallo
   ```

**Réponse :**
```json
{
  "success": true,
  "data": [
    {
      "id": "uuid",
      "numeroCompte": "C00123456",
      "titulaire": "Amadou Diallo",
      "type": "epargne",
      "solde": 1250000,
      "devise": "FCFA",
      "dateCreation": "2023-03-15T00:00:00Z",
      "statut": "bloque",
      "motifBlocage": "Inactivité de 30+ jours",
      "dateDebutBlocage": "2025-09-09T05:13:07Z",
      "dateFinBlocage": "2026-01-24T05:13:07Z",
      "metadata": {
        "derniereModification": "2025-10-14T05:13:07Z",
        "version": 2
      }
    }
  ],
  "pagination": {
    "currentPage": 1,
    "totalPages": 6,
    "totalItems": 58,
    "itemsPerPage": 10,
    "hasNext": true,
    "hasPrevious": false
  },
  "links": {
    "self": "/api/v1/comptes?page=1&limit=10",
    "first": "/api/v1/comptes?page=1&limit=10",
    "last": "/api/v1/comptes?page=6&limit=10",
    "next": "/api/v1/comptes?page=2&limit=10",
    "prev": null
  }
}
```

#### GET `/comptes/{compteId}`
Récupérer un compte spécifique.

**Headers :**
```
Accept: application/json
Authorization: Bearer {token}
```

**Exemple :**
```
GET /api/v1/comptes/uuid-du-compte
```

#### POST `/comptes`
Créer un nouveau compte.

**Headers :**
```
Accept: application/json
Content-Type: application/json
Authorization: Bearer {token}
```

**Body :**
```json
{
  "type": "cheque",
  "soldeInitial": 500000,
  "devise": "FCFA",
  "client": {
    "id": "uuid-client-existant",
    "titulaire": "Nouveau Client",
    "email": "nouveau@example.com",
    "telephone": "+221771234568",
    "adresse": "Dakar, Sénégal"
  }
}
```

**Ou pour client existant :**
```json
{
  "type": "epargne",
  "soldeInitial": 1000000,
  "devise": "FCFA",
  "client": {
    "id": "uuid-client"
  }
}
```

#### PATCH `/comptes/{compteId}`
Mettre à jour les informations du client associé au compte.

**Headers :**
```
Accept: application/json
Content-Type: application/json
Authorization: Bearer {token}
```

**Body :**
```json
{
  "titulaire": "Nouveau Nom",
  "informationsClient": {
    "telephone": "+221771234569",
    "email": "nouveau.email@example.com"
  }
}
```

#### DELETE `/comptes/{compteId}`
Supprimer un compte (soft delete).

**Headers :**
```
Accept: application/json
Authorization: Bearer {token}
```

#### POST `/comptes/{compteId}/bloquer`
Bloquer un compte épargne actif.

**Headers :**
```
Accept: application/json
Content-Type: application/json
Authorization: Bearer {token}
```

**Body :**
```json
{
  "motif": "Activité suspecte détectée",
  "duree": 30,
  "unite": "mois"
}
```

#### POST `/comptes/{compteId}/debloquer`
Débloquer un compte bloqué.

**Headers :**
```
Accept: application/json
Content-Type: application/json
Authorization: Bearer {token}
```

**Body :**
```json
{
  "motif": "Vérification complétée"
}
```

## 🧪 Données de Test

### Clients disponibles pour les tests :

1. **Client Test 1**
   - Email: `client1@example.com`
   - Password: `password123`
   - Code: `ABC123` (pour première connexion)

2. **Client Test 2**
   - Email: `client2@example.com`
   - Password: `password123`
   - Code: `DEF456`

3. **Client Test 3**
   - Email: `client3@example.com`
   - Password: `password123`
   - Code: `GHI789`

### Comptes de test :
- **58 comptes** créés automatiquement
- **18 comptes actifs**, **21 bloqués**, **19 fermés**
- **31 comptes épargne**, **27 comptes chèque**
- Soldes variant de 10 000 à 1 000 000 FCFA

## 🚀 Comment Tester avec Postman

### 1. Configuration de l'environnement Postman

Créer un nouvel environnement avec :
```json
{
  "base_url": "http://127.0.0.1:8000/api/v1",
  "token": ""
}
```

### 2. Collection Postman

Importer cette collection ou créer manuellement :

#### 📁 **API Banque**
- **Auth**
  - `POST {{base_url}}/auth/login`
  - `POST {{base_url}}/auth/refresh`
  - `POST {{base_url}}/auth/logout`

- **Comptes**
  - `GET {{base_url}}/comptes`
  - `GET {{base_url}}/comptes/:id`
  - `POST {{base_url}}/comptes`
  - `PATCH {{base_url}}/comptes/:id`
  - `DELETE {{base_url}}/comptes/:id`
  - `POST {{base_url}}/comptes/:id/bloquer`
  - `POST {{base_url}}/comptes/:id/debloquer`

### 3. Workflow de test typique

1. **Login :**
   ```
   POST {{base_url}}/auth/login
   Body: {"email": "client1@example.com", "password": "password123", "code": "ABC123"}
   ```
   → Copier le token dans les variables d'environnement

2. **Lister les comptes :**
   ```
   GET {{base_url}}/comptes
   Header: Authorization: Bearer {{token}}
   ```

3. **Tester les filtres :**
   ```
   GET {{base_url}}/comptes?statut=actif&type=epargne&limit=3
   Header: Authorization: Bearer {{token}}
   ```

4. **Créer un compte :**
   ```
   POST {{base_url}}/comptes
   Header: Authorization: Bearer {{token}}
   Body: {...}
   ```

## ⚠️ Gestion des Erreurs

### Codes d'erreur courants :

- **401 Unauthorized** : Token manquant ou invalide
- **403 Forbidden** : Permissions insuffisantes ou accès refusé
- **404 Not Found** : Ressource non trouvée
- **422 Unprocessable Entity** : Données de validation invalides
- **429 Too Many Requests** : Limite de taux dépassée (10 req/jour)
- **500 Internal Server Error** : Erreur serveur

### Structure des erreurs :
```json
{
  "success": false,
  "error": {
    "code": "VALIDATION_ERROR",
    "message": "Les données fournies sont invalides",
    "details": {
      "email": ["L'adresse email est déjà utilisée"]
    }
  }
}
```

## 🔒 Sécurité

- **Rate Limiting** : 10 requêtes par jour par utilisateur
- **Authentification** : Laravel Sanctum avec tokens
- **Validation** : Règles strictes pour données sensibles
- **Soft Deletes** : Suppression logique des comptes
- **Logs** : Utilisateurs dépassant les limites sont loggés

## 📊 Statistiques de l'API

- **Routes implémentées** : 10 endpoints
- **Modèles** : Client, Compte
- **Middlewares** : RateLimit, Role, ApiResponse
- **Resources** : CompteResource, CompteCollection
- **Validations** : StoreClientRequest, StoreCompteRequest
- **Tests data** : 20 clients + 58 comptes

## 🎯 Prochaines Étapes

Une fois ces endpoints testés et validés :
1. Implémenter les transactions
2. Ajouter les jobs pour archivage Neon
3. Configurer Swagger pour documentation interactive
4. Déployer sur Vercel

---

**✨ Prêt à tester !** Lancez `php artisan serve` et commencez par l'authentification.