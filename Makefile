#---VARIABLES---------------------------------#
#---DOCKER---#
DOCKER = docker
DOCKER_RUN = $(DOCKER) run
DOCKER_COMPOSE = docker compose
DOCKER_COMPOSE_UP = $(DOCKER_COMPOSE) up -d
DOCKER_COMPOSE_STOP = $(DOCKER_COMPOSE) stop
#------------#

#---SYMFONY--#
SYMFONY = symfony
SYMFONY_SERVER_START = $(SYMFONY) serve -d
SYMFONY_SERVER_STOP = $(SYMFONY) server:stop
SYMFONY_CONSOLE = $(SYMFONY) console
SYMFONY_LINT = $(SYMFONY_CONSOLE) lint:
#------------#

#---COMPOSER-#
COMPOSER = composer
COMPOSER_INSTALL = $(COMPOSER) install
COMPOSER_UPDATE = $(COMPOSER) update
#------------#

#---NPM-----#
NPM = npm
NPM_INSTALL = $(NPM) install --force
NPM_UPDATE = $(NPM) update
NPM_BUILD = $(NPM) run build
NPM_DEV = $(NPM) run dev
NPM_WATCH = $(NPM) run watch
#------------#

#---PHPQA---#
PHPQA = jakzal/phpqa
PHPQA_RUN = $(DOCKER_RUN) --init -it --rm -v $(PWD):/project -w /project $(PHPQA)
#------------#

#---PHPUNIT-#
PHPUNIT = APP_ENV=test $(SYMFONY) php bin/phpunit
#------------#
#---------------------------------------------#
## === 🆘  HELP ==================================================
help: ## Show this help.
	@echo "Symfony-And-Docker-Makefile"
	@echo "---------------------------"
	@echo "Usage: make [target]"
	@echo ""
	@echo "Targets:"
	@grep -E '(^[a-zA-Z0-9_-]+:.*?##.*$$)|(^##)' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}{printf "\033[32m%-30s\033[0m %s\n", $$1, $$2}' | sed -e 's/\[32m##/[33m/'
#---------------------------------------------#
## === 🐋  DOCKER ================================================
docker-up: ## Start docker containers.
	$(DOCKER_COMPOSE_UP)
.PHONY: docker-up

docker-down: ## Stop docker containers.
	$(DOCKER_COMPOSE_STOP)
.PHONY: docker-down

#---------------------------------------------#
## === 🆘  HELP ==================================================
sf: ## List and Use All Symfony commands (make sf command="commande-name").
	$(SYMFONY_CONSOLE) $(command)
.PHONY: sf

sf-start: ## Start symfony server.
	$(SYMFONY_SERVER_START)
.PHONY: sf-start

sf-stop: ## Stop symfony server.
	$(SYMFONY_SERVER_STOP)
.PHONY: sf-stop

sf-cc: ## Clear symfony cache.
	$(SYMFONY_CONSOLE) cache:clear && $(SYMFONY_CONSOLE) chmod:cache
.PHONY: sf-cc

sf-log: ## Show symfony logs.
	$(SYMFONY) server:log
.PHONY: sf-log

sf-dc: ## Create symfony database.
	$(SYMFONY_CONSOLE) doctrine:database:create --if-not-exists
.PHONY: sf-dc

sf-dd: ## Drop symfony database.
	$(SYMFONY_CONSOLE) doctrine:database:drop --if-exists --force
.PHONY: sf-dd

sf-db-init: ## Init the database
	$(SYMFONY_CONSOLE) doctrine:database:drop --if-exists --force && $(SYMFONY_CONSOLE) doctrine:database:create --if-not-exists
.PHONY: sf-db-init

sf-su: ## Update symfony schema database.
	$(SYMFONY_CONSOLE) doctrine:schema:update --force
.PHONY: sf-su

sf-migration: ## Make migrations and migrate.
	$(SYMFONY_CONSOLE) make:migration && $(SYMFONY_CONSOLE) doctrine:migrations:migrate --no-interaction
.PHONY: sf-migration

sf-mm: ## Make migrations.
	$(SYMFONY_CONSOLE) make:migration
.PHONY: sf-mm

sf-dmm: ## Migrate.
	$(SYMFONY_CONSOLE) doctrine:migrations:migrate --no-interaction
.PHONY: sf-dmm

sf-migration-down: ## Rollback to the previous migration.
	$(SYMFONY_CONSOLE) doctrine:migrations:migrate prev
.PHONY: sf-migration-down

sf-voter: ## create voter.
	$(SYMFONY_CONSOLE) make:voter
.PHONY: sf-voter

sf-me: ## Make symfony entity
	$(SYMFONY_CONSOLE) make:entity
.PHONY: sf-me

sf-mc: ## Make symfony controller
	$(SYMFONY_CONSOLE) make:controller --no-template
.PHONY: sf-mc

sf-mf: ## Make symfony Form
	$(SYMFONY_CONSOLE) make:form
.PHONY: sf-mf

sf-perm: ## Fix permissions.
	chmod -R 777 var
.PHONY: sf-perm

sf-sudo-perm: ## Fix permissions with sudo.
	sudo chmod -R 777 var
.PHONY: sf-sudo-perm

sf-dump-env: ## Dump env.
	$(SYMFONY_CONSOLE) debug:dotenv
.PHONY: sf-dump-env

sf-dump-env-container: ## Dump Env container.
	$(SYMFONY_CONSOLE) debug:container --env-vars
.PHONY: sf-dump-env-container

sf-dump-routes: ## Dump routes.
	$(SYMFONY_CONSOLE) debug:router
.PHONY: sf-dump-routes

sf-open: ## Open project in a browser.
	$(SYMFONY) open:local
.PHONY: sf-open

sf-open-email: ## Open Email catcher.
	$(SYMFONY) open:local:webmail
.PHONY: sf-open-email

sf-check-requirements: ## Check requirements.
	$(SYMFONY) check:requirements
.PHONY: sf-check-requirements
#---------------------------------------------#
## === 📦  COMPOSER ==============================================
composer-install: ## Install composer dependencies.
	$(COMPOSER_INSTALL)
.PHONY: composer-install

composer-update: ## Update composer dependencies.
	 git "pull" && $(COMPOSER_UPDATE)
.PHONY: composer-update

composer-validate: ## Validate composer.json file.
	$(COMPOSER) validate
.PHONY: composer-validate

composer-validate-deep: ## Validate composer.json and composer.lock files in strict mode.
	$(COMPOSER) validate --strict --check-lock
.PHONY: composer-validate-deep
#---------------------------------------------#
## === 🐛  PHPQA =================================================
qa-cs-fixer-dry-run: ## Run php-cs-fixer in dry-run mode.
	$(PHPQA_RUN) php-cs-fixer fix ./src --rules=@Symfony --verbose --dry-run
.PHONY: qa-cs-fixer-dry-run

qa-cs-fixer: ## Run php-cs-fixer.
	$(PHPQA_RUN) php-cs-fixer fix ./src --rules=@Symfony --verbose
.PHONY: qa-cs-fixer

qa-phpstan: ## Run phpstan.
	$(PHPQA_RUN) phpstan analyse ./src --level=7
.PHONY: qa-phpstan

qa-security-checker: ## Run security-checker.
	$(SYMFONY) security:check
.PHONY: qa-security-checker

qa-phpcpd: ## Run phpcpd (copy/paste detector).
	$(PHPQA_RUN) phpcpd ./src
.PHONY: qa-phpcpd

qa-php-metrics: ## Run php-metrics.
	$(PHPQA_RUN) phpmetrics --report-html=var/phpmetrics ./src
.PHONY: qa-php-metrics

qa-lint-twigs: ## Lint twig files.
	$(SYMFONY_LINT)twig ./templates
.PHONY: qa-lint-twigs

qa-lint-yaml: ## Lint yaml files.
	$(SYMFONY_LINT)yaml ./config
.PHONY: qa-lint-yaml

qa-lint-container: ## Lint container.
	$(SYMFONY_LINT)container
.PHONY: qa-lint-container

qa-lint-schema: ## Lint Doctrine schema.
	$(SYMFONY_CONSOLE) doctrine:schema:validate --skip-sync -vvv --no-interaction
.PHONY: qa-lint-schema

qa-audit: ## Run composer audit.
	$(COMPOSER) audit
.PHONY: qa-audit
#---------------------------------------------#
## === 🔎  TESTS =================================================
tests: ## Run tests.
	$(PHPUNIT) --testdox
.PHONY: tests

tests-coverage: ## Run tests with coverage.
	$(PHPUNIT) --coverage-html var/coverage
.PHONY: tests-coverage
#---------------------------------------------#
## === ⭐  OTHERS =================================================
before-commit: qa-cs-fixer qa-phpstan qa-security-checker qa-phpcpd qa-lint-twigs qa-lint-yaml qa-lint-container qa-lint-schema tests ## Run before commit.
.PHONY: before-commit

first-install: docker-up composer-install npm-install npm-build sf-perm sf-dc sf-dmm sf-start sf-open ## First install.
.PHONY: first-install

start: docker-up sf-start sf-open ## Start project.
.PHONY: start

dev: docker-up sf-start ## Start project.
.PHONY: dev

stop: docker-down sf-stop ## Stop project.
.PHONY: stop

reset-db: ## Reset database.
	$(eval CONFIRM := $(shell read -p "Are you sure you want to reset the database? [y/N] " CONFIRM && echo $${CONFIRM:-N}))
	@if [ "$(CONFIRM)" = "y" ]; then \
		$(MAKE) sf-dd; \
		$(MAKE) sf-dc; \
		$(MAKE) sf-dmm; \
	fi
.PHONY: reset-db
#---------------------------------------------#
## === 🖥️  FRONT ==================================================
front: ## Update, git pull and launch the frontend
	cd ".." && cd "sunstack-front" && git "pull" && npm "update" "--save" && npm "run" "dev"
.PHONY: front
prettier: ## format all front code
	cd ".." && cd "sunstack-front" && npm "run" "prettier:write"
.PHONY: prettier
#---------------------------------------------#