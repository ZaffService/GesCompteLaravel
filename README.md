# API Banque - Laravel Banking API

Une API REST complète pour la gestion bancaire développée avec Laravel, PostgreSQL et Redis.

## 🚀 Déploiement sur Render

### Prérequis
- Un compte Render (https://render.com)
- Un compte GitHub pour connecter votre repository

### Étapes de déploiement

1. **Pousser votre code sur GitHub**
   ```bash
   git add .
   git commit -m "Ready for production deployment"
   git push origin main
   ```

2. **Créer un nouveau service sur Render**
   - Connectez-vous à Render
   - Cliquez sur "New +" → "Web Service"
   - Connectez votre repository GitHub
   - Sélectionnez le repository `Laravel-10`

3. **Configuration du service**
   - **Name**: `api-banque` (ou votre choix)
   - **Runtime**: Docker
   - **Dockerfile Path**: `./Dockerfile`
   - **Docker Context**: `.`
   - **Plan**: Starter (gratuit)

4. **Configuration des variables d'environnement**
   Dans l'onglet "Environment", ajoutez ces variables secrètes :

   | Variable | Valeur | Description |
   |----------|--------|-------------|
   | `APP_KEY` | Générer une clé Laravel | `php artisan key:generate --show` |
   | `APP_URL` | URL de votre service Render | Ex: `https://api-banque.onrender.com` |
   | `MAIL_HOST` | Votre serveur SMTP | Ex: `smtp.gmail.com` |
   | `MAIL_USERNAME` | Votre email | |
   | `MAIL_PASSWORD` | Mot de passe d'application | |
   | `MAIL_FROM_ADDRESS` | Votre email | |

5. **Déployer**
   - Cliquez sur "Create Web Service"
   - Render va automatiquement :
     - Builder votre image Docker
     - Créer la base de données PostgreSQL
     - Configurer Redis
     - Déployer l'application

6. **Vérifier le déploiement**
   - Une fois déployé, accédez à : `https://votre-service.onrender.com`
   - La documentation Swagger sera disponible à : `https://votre-service.onrender.com/api/documentation`

## 🔍 Liens de test après déploiement

### URLs à tester :

1. **Interface Swagger UI** :
   ```
   https://moustapha-seck.onrender.com/docs
   ```
   - Devrait afficher l'interface Swagger avec tous les endpoints

2. **Documentation JSON (via query parameter)** :
   ```
   https://moustapha-seck.onrender.com/docs/?api-docs.json
   ```
   - Devrait retourner le fichier JSON OpenAPI directement

3. **Documentation JSON (via route directe)** :
   ```
   https://moustapha-seck.onrender.com/docs/api-docs.json
   ```
   - Alternative pour accéder au JSON

4. **Page d'accueil** :
   ```
   https://moustapha-seck.onrender.com/
   ```
   - Page de bienvenue Laravel

### Tests fonctionnels :

5. **Authentification (Admin)** :
   ```bash
   curl -X POST https://moustapha-seck.onrender.com/api/v1/auth/login \
     -H "Content-Type: application/json" \
     -d '{"email":"admin@banque.com","password":"admin123"}'
   ```

6. **Authentification (Client)** :
   ```bash
   curl -X POST https://moustapha-seck.onrender.com/api/v1/auth/login \
     -H "Content-Type: application/json" \
     -d '{"email":"client@example.com","password":"client123","code":"123456"}'
   ```

7. **Lister les comptes (avec token)** :
   ```bash
   curl -X GET https://moustapha-seck.onrender.com/api/v1/comptes \
     -H "Authorization: Bearer YOUR_JWT_TOKEN"
   ```

### Vérifications importantes :

- ✅ L'interface Swagger UI se charge correctement
- ✅ Le champ "Explore" dans Swagger affiche : `https://moustapha-seck.onrender.com/docs/?api-docs.json`
- ✅ Cette URL retourne bien du JSON valide
- ✅ Les endpoints d'authentification fonctionnent
- ✅ Les endpoints protégés nécessitent un token valide

## 🔧 Configuration locale (Docker)

### Prérequis
- Docker et Docker Compose installés
- Variables d'environnement configurées

### Démarrage
```bash
# Copier le fichier d'exemple d'environnement
cp .env.example .env

# Éditer .env avec vos valeurs locales
nano .env

# Démarrer les services
docker compose up -d

# Vérifier que tout fonctionne
docker compose ps
```

### Accès local
- **API**: http://localhost:8081
- **Swagger UI**: http://localhost:8081/api/documentation
- **Base de données**: localhost:5433
- **Redis**: localhost:6380

## 📚 Documentation API

L'API utilise Swagger/OpenAPI pour la documentation. Une fois déployée, accédez à `/api/documentation` pour explorer tous les endpoints.

### Endpoints principaux
- `POST /api/v1/auth/login` - Authentification
- `GET /api/v1/comptes` - Lister les comptes
- `POST /api/v1/comptes` - Créer un compte
- `GET /api/v1/comptes/{id}` - Détails d'un compte
- `PATCH /api/v1/comptes/{id}` - Modifier un compte
- `DELETE /api/v1/comptes/{id}` - Supprimer un compte
- `POST /api/v1/comptes/{id}/bloquer` - Bloquer un compte
- `POST /api/v1/comptes/{id}/debloquer` - Débloquer un compte

## 🔐 Authentification

L'API utilise OAuth2 avec Passport. Pour accéder aux endpoints protégés :
1. Authentifiez-vous via `POST /api/v1/auth/login`
2. Utilisez le token JWT dans le header `Authorization: Bearer {token}`

## 🗄️ Base de données

- **PostgreSQL** pour les données persistantes
- **Redis** pour le cache et les sessions
- Migrations et seeders inclus pour initialiser la structure

## 📧 Email

Configuration SMTP pour les notifications (optionnel).

## 🐳 Architecture Docker

- **App**: PHP 8.3 + Nginx + Supervisor
- **DB**: PostgreSQL 14
- **Cache**: Redis 7 Alpine

## 🚀 Optimisations production

- Cache des configurations, routes et vues
- Optimisation autoloader Composer
- Compression des assets statiques
- Rate limiting activé
- Logs structurés

## 📝 Support

Pour toute question concernant le déploiement ou l'utilisation de l'API, consultez la documentation Swagger ou créez une issue sur le repository.
