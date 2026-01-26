APP_NAME = web-app-project-skeleton
VERSION = 0.1.1

build: image up open

image:
	@echo "Build development image"
	docker build . -f ./build/php/Dockerfile --target dev -t ${APP_NAME}-dev:${VERSION}
	
up:
	@echo "Boot stack"
	docker compose up -d --remove-orphans

down:
	@echo "Shutting down"
	docker compose down

restart: reset
reset: reset-worker reset-app

reset-worker:
	@echo "Reset worker"
	docker compose restart worker

reset-app:
	@echo "Reset app"
	docker compose restart app

init: composer-install create-database create-schema load-fixtures

composer-install:
	@echo "Install composer dependencies"
	docker compose exec -it app composer install

create-database:
	@echo "Create database"
	docker compose exec -it app bin/console doctrine:database:create --if-not-exists

load-fixtures:
	@echo "Load fixtures"
	docker compose exec -it app bin/console doctrine:fixtures:load -q

create-schema:
	@echo "Create database schema"
	docker compose exec -it app bin/console doctrine:schema:update --force

init-test: create-test-database create-test-schema

create-test-database:
	@echo "Create database"
	docker compose exec -it app bin/console doctrine:database:create --env=test --if-not-exists

create-test-schema:
	@echo "Create database schema"
	docker compose exec -it app bin/console doctrine:schema:update --env=test --force

composer:
	@echo "Run composer"
	docker compose exec -it app composer $(cmd)

shell: shell-backend
shell-backend: backend-shell
backend-shell:
	@echo "Open shell on app container"
	docker compose exec -it app bash
	
qa: quality
quality:
	@echo "Run quality scripts"
	docker compose exec -it app composer qa

sa: phpstan
phpstan:
	@echo "Run static code analysis"
	docker compose exec -it app vendor/bin/phpstan analyse --memory-limit=1G

cs: style
style: codestyle
codestyle: code-style
code-style:
	@echo "Fix code style"
	docker compose exec -it app vendor/bin/php-cs-fixer fix

test: test-backend

backend-test: test-backend
test-backend:
	@echo "Run backend tests"
	docker compose exec -it app bin/phpunit
	
arch:
	@echo "Test architecture"
	docker compose exec -it app vendor/bin/deptrac analyse --report-uncovered

clear:
	@echo "Clear all caches"
	docker compose exec -it app composer clear

maintenance: maintain
maintain: show-composer-updates update-composer-dependencies update-npm-dependencies

show-composer-updates:
	@echo "Show wether composer dependencies are outdated"
	docker compose exec -it app composer show --outdated
	
update-composer-dependencies:
	@echo "Update dependencies"
	docker compose exec -it app composer update -W

update-npm-dependencies:
	@echo "Update NPM dependencies"
	docker compose exec -it npm-dev npm update --save

coverage:
	@echo "Generate coverage report"
	docker compose exec -it app bin/phpunit -c phpunit.xml.dist --coverage-html ./coverage

npm-build:
	@echo "Create frontend build"
	docker compose exec -it npm-dev npm run build

open:
	@if command -v xdg-open > /dev/null 2>&1; then \
		xdg-open http://localhost:8000 2>/dev/null & \
	elif command -v open > /dev/null 2>&1; then \
		open http://localhost:8000; \
	elif command -v wslview > /dev/null 2>&1; then \
		wslview http://localhost:8000; \
	elif command -v cmd.exe > /dev/null 2>&1; then \
		cmd.exe /c start http://localhost:8000; \
	else \
		echo "❌ Could not detect browser launcher."; \
		echo "📍 Please open http://localhost:8000 manually"; \
	fi
