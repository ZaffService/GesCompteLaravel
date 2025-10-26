-- Initialisation de la base de données PostgreSQL pour l'API Banque

-- Créer l'extension pour les UUID si nécessaire
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";

-- Créer l'extension pour les générer des mots de passe cryptés
CREATE EXTENSION IF NOT EXISTS "pgcrypto";

-- Créer un rôle pour l'application avec des permissions limitées
-- Note: Le rôle principal est créé via docker-compose environment variables

-- Configuration des paramètres de base de données
ALTER DATABASE banque_api SET timezone = 'UTC';

-- Créer des indexes pour optimiser les performances
-- Ces indexes seront créés automatiquement par les migrations Laravel,
-- mais nous pouvons les pré-définir si nécessaire

-- Index pour la table users (admins)
CREATE INDEX CONCURRENTLY IF NOT EXISTS idx_users_email ON users(email);
CREATE INDEX CONCURRENTLY IF NOT EXISTS idx_users_role ON users(role);

-- Index pour la table clients
CREATE INDEX CONCURRENTLY IF NOT EXISTS idx_clients_email ON clients(email);
CREATE INDEX CONCURRENTLY IF NOT EXISTS idx_clients_code ON clients(code);
CREATE INDEX CONCURRENTLY IF NOT EXISTS idx_clients_role ON clients(role);

-- Index pour la table comptes
CREATE INDEX CONCURRENTLY IF NOT EXISTS idx_comptes_client_id ON comptes(client_id);
CREATE INDEX CONCURRENTLY IF NOT EXISTS idx_comptes_numero ON comptes(numero_compte);
CREATE INDEX CONCURRENTLY IF NOT EXISTS idx_comptes_statut ON comptes(statut);
CREATE INDEX CONCURRENTLY IF NOT EXISTS idx_comptes_type ON comptes(type);

-- Index pour les tables OAuth de Passport
CREATE INDEX CONCURRENTLY IF NOT EXISTS idx_oauth_access_tokens_user_id ON oauth_access_tokens(user_id);
CREATE INDEX CONCURRENTLY IF NOT EXISTS idx_oauth_access_tokens_client_id ON oauth_access_tokens(client_id);
CREATE INDEX CONCURRENTLY IF NOT EXISTS idx_oauth_access_tokens_revoked ON oauth_access_tokens(revoked);

-- Configuration des droits
-- Note: Les droits sont gérés par docker-compose

-- Message de confirmation
DO $$
BEGIN
    RAISE NOTICE 'Base de données API Banque initialisée avec succès';
END
$$;
