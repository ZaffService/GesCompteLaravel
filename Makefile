# Makefile pour l'API Banque - Gestion simplifiée des tâches Docker

.PHONY: help build up down restart logs clean install test deploy

# Variables
DOCKER_COMPOSE = docker-compose
DOCKER_COMPOSE_PROD = docker-compose -f docker-compose.prod.yml
APP_CONTAINER = api-banque-app
DB_CONTAINER = api-banque-db
REDIS_CONTAINER = api-banque-redis

# Couleurs pour les messages
GREEN = \033[0;32m
YELLOW = \033[1;33m
RED = \033[0;31m
NC = \033[0m # No Color

# Aide
help: ## Afficher cette aide
	@echo "$(GREEN)API Banque - Commandes Docker disponibles:$(NC)"
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "  $(YELLOW)%-20s$(NC) %s\n", $$1, $$2}'

# Développement
build: ## Construire les images Docker
	@echo "$(GREEN)🔨 Construction des images Docker...$(NC)"
	$(DOCKER_COMPOSE) build --no-cache

up: ## Démarrer les services en mode développement
	@echo "$(GREEN)🚀 Démarrage des services en développement...$(NC)"
	$(DOCKER_COMPOSE) up -d
	@echo "$(GREEN)✅ Services démarrés !$(NC)"
	@echo "$(YELLOW)📱 Application disponible sur: http://localhost$(NC)"
	@echo "$(YELLOW)📚 Documentation API: http://localhost/api/documentation$(NC)"

down: ## Arrêter les services
	@echo "$(RED)🛑 Arrêt des services...$(NC)"
	$(DOCKER_COMPOSE) down

restart: ## Redémarrer les services
	@echo "$(YELLOW)🔄 Redémarrage des services...$(NC)"
	$(DOCKER_COMPOSE) restart

logs: ## Afficher les logs des services
	$(DOCKER_COMPOSE) logs -f

logs-app: ## Afficher les logs de l'application
	$(DOCKER_COMPOSE) logs -f $(APP_CONTAINER)

logs-db: ## Afficher les logs de la base de données
	$(DOCKER_COMPOSE) logs -f $(DB_CONTAINER)

logs-redis: ## Afficher les logs Redis
	$(DOCKER_COMPOSE) logs -f $(REDIS_CONTAINER)

# Maintenance
clean: ## Nettoyer les conteneurs, volumes et images
	@echo "$(RED)🧹 Nettoyage complet...$(NC)"
	$(DOCKER_COMPOSE) down -v --rmi all
	docker system prune -f

install: ## Installer les dépendances et initialiser l'application
	@echo "$(GREEN)📦 Installation des dépendances...$(NC)"
	$(DOCKER_COMPOSE) exec $(APP_CONTAINER) composer install --optimize-autoloader --no-dev
	@echo "$(GREEN)🔑 Génération de la clé d'application...$(NC)"
	$(DOCKER_COMPOSE) exec $(APP_CONTAINER) php artisan key:generate
	@echo "$(GREEN)📚 Génération de la documentation Swagger...$(NC)"
	$(DOCKER_COMPOSE) exec $(APP_CONTAINER) php artisan l5-swagger:generate

migrate: ## Exécuter les migrations de base de données
	@echo "$(GREEN)📦 Exécution des migrations...$(NC)"
	$(DOCKER_COMPOSE) exec $(APP_CONTAINER) php artisan migrate --force

seed: ## Exécuter les seeders
	@echo "$(GREEN)🌱 Exécution des seeders...$(NC)"
	$(DOCKER_COMPOSE) exec $(APP_CONTAINER) php artisan db:seed --force

fresh: ## Réinitialiser la base de données
	@echo "$(YELLOW)🔄 Réinitialisation de la base de données...$(NC)"
	$(DOCKER_COMPOSE) exec $(APP_CONTAINER) php artisan migrate:fresh --seed --force

# Tests
test: ## Exécuter les tests
	@echo "$(GREEN)🧪 Exécution des tests...$(NC)"
	$(DOCKER_COMPOSE) exec $(APP_CONTAINER) php artisan test

test-coverage: ## Exécuter les tests avec couverture
	@echo "$(GREEN)🧪 Exécution des tests avec couverture...$(NC)"
	$(DOCKER_COMPOSE) exec $(APP_CONTAINER) php artisan test --coverage

# Production
build-prod: ## Construire les images pour la production
	@echo "$(GREEN)🔨 Construction des images de production...$(NC)"
	$(DOCKER_COMPOSE_PROD) build --no-cache

deploy-prod: ## Déployer en production
	@echo "$(GREEN)🚀 Déploiement en production...$(NC)"
	$(DOCKER_COMPOSE_PROD) up -d
	@echo "$(GREEN)✅ Application déployée en production !$(NC)"

# Outils
shell: ## Accéder au shell du conteneur de l'application
	$(DOCKER_COMPOSE) exec $(APP_CONTAINER) bash

shell-db: ## Accéder au shell PostgreSQL
	$(DOCKER_COMPOSE) exec $(DB_CONTAINER) psql -U banque_user -d banque_api

cache-clear: ## Vider le cache de l'application
	@echo "$(YELLOW)🗑️  Vidage du cache...$(NC)"
	$(DOCKER_COMPOSE) exec $(APP_CONTAINER) php artisan cache:clear
	$(DOCKER_COMPOSE) exec $(APP_CONTAINER) php artisan config:clear
	$(DOCKER_COMPOSE) exec $(APP_CONTAINER) php artisan route:clear
	$(DOCKER_COMPOSE) exec $(APP_CONTAINER) php artisan view:clear

optimize: ## Optimiser l'application pour la production
	@echo "$(GREEN)⚡ Optimisation de l'application...$(NC)"
	$(DOCKER_COMPOSE) exec $(APP_CONTAINER) php artisan config:cache
	$(DOCKER_COMPOSE) exec $(APP_CONTAINER) php artisan route:cache
	$(DOCKER_COMPOSE) exec $(APP_CONTAINER) php artisan view:cache
	$(DOCKER_COMPOSE) exec $(APP_CONTAINER) php artisan l5-swagger:generate

# Monitoring
status: ## Afficher le statut des services
	@echo "$(GREEN)📊 Statut des services:$(NC)"
	$(DOCKER_COMPOSE) ps

health: ## Vérifier la santé des services
	@echo "$(GREEN)🏥 Vérification de la santé:$(NC)"
	@curl -s http://localhost/health || echo "$(RED)❌ Service indisponible$(NC)"
	@echo "$(GREEN)✅ Service opérationnel$(NC)"

# Sécurité
security-check: ## Vérifier la sécurité des conteneurs
	@echo "$(GREEN)🔒 Vérification de sécurité...$(NC)"
	$(DOCKER_COMPOSE) exec $(APP_CONTAINER) php artisan tinker --execute="echo 'Security check passed'"

# Backup
backup-db: ## Sauvegarder la base de données
	@echo "$(GREEN)💾 Sauvegarde de la base de données...$(NC)"
	$(DOCKER_COMPOSE) exec $(DB_CONTAINER) pg_dump -U banque_user banque_api > backup_$(shell date +%Y%m%d_%H%M%S).sql
	@echo "$(GREEN)✅ Sauvegarde terminée$(NC)"
