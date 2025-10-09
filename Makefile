up: ## Start all containers
	docker compose up -d

down: ## Stop containers
	docker compose down

rebuild: ## Rebuild containers
	docker compose up -d --build

seed: ## Run migrations + seeders
	docker compose exec app php artisan migrate --seed

fix: ## Run Laravel Pint
	docker compose exec app ./vendor/bin/pint

stan: ## Run PHPStan
	docker compose exec app ./vendor/bin/phpstan analyse

test: ## Run Pest tests
	docker compose exec app ./vendor/bin/pest
