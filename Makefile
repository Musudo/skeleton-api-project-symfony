DC = docker compose

.PHONY: help up down build sh logs ps worker test stan cs-fix cs-check
stan: ## Static analysis (PHPStan, level max)
	$(DC) exec -T php vendor/bin/phpstan analyse --memory-limit=512M
cs-fix: ## Auto-fix code style
	$(DC) exec -T php vendor/bin/php-cs-fixer fix
cs-check: ## Check code style without modifying files
	$(DC) exec -T php vendor/bin/php-cs-fixer fix --dry-run --diff
help: ## List targets
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | awk 'BEGIN{FS=":.*?## "}{printf "  \033[36m%-8s\033[0m %s\n", $$1, $$2}'
up: ## Build & start the dev stack, waiting for healthchecks
	$(DC) up -d --build --wait
down: ## Stop and remove the stack
	$(DC) down --remove-orphans
build: ## Rebuild images from scratch
	$(DC) build --pull --no-cache
sh: ## Shell into the php container
	$(DC) exec php sh
logs: ## Tail all logs
	$(DC) logs -f
ps: ## Service status
	$(DC) ps
worker: ## Start the Messenger worker (enable after Step 7)
	$(DC) --profile worker up -d worker
test: ## Run the full test suite
	$(DC) exec -T php vendor/bin/phpunit