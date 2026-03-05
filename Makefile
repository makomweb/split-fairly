APP_NAME = split-fairly
VERSION = 0.1.2

.DEFAULT_GOAL := help

help:
	@echo "📋 Available targets:\n"
	@echo "🏗️  Build & Setup:"
	@echo "  make start - Build development image, boot stack, initialize db, and open browser"
	@echo "  make build - Build development Docker image"
	@echo "  make prod - Build production Docker images (app + web)"
	@echo "  make up - Boot the Docker stack"
	@echo "  make down - Shut down the Docker stack"
	@echo "  make init - Initialize app (composer, database, fixtures)"
	@echo "\n🔄 Maintenance:"
	@echo "  make maintain - Update composer and npm dependencies"
	@echo "  make show-composer-updates - Show outdated composer packages"
	@echo "  make update-composer-dependencies - Update composer packages"
	@echo "  make update-npm-dependencies - Update npm packages"
	@echo "\n🧪 Testing & Quality:"
	@echo "  make test - Run backend and frontend tests"
	@echo "  make quality - Run quality checks"
	@echo "  make phpstan - Run static code analysis"
	@echo "  make style - Fix code style"
	@echo "  make arch - Test architecture"
	@echo "  make coverage - Generate coverage report"
	@echo "\n🛠️  Development:"			
	@echo "  make shell - Open shell on app container"
	@echo "  make composer - Run composer command (use: make composer cmd='install')"
	@echo "  make npm-build - Create frontend build"
	@echo "  make clear - Clear all caches"
	@echo "  make open - Open application in browser\n"

start: build up init open

build:
	@echo "🏗️  Building development image(s)..."
	docker build . -f ./build/php/Dockerfile --target dev --no-cache -t ${APP_NAME}-dev:${VERSION}

prod:
	@echo "🛳️  Building procution image(s)..."
	docker build . -f ./build/php/Dockerfile --target prod --no-cache -t ${APP_NAME}:${VERSION}
	docker build . -f ./build/nginx/Dockerfile --no-cache -t ${APP_NAME}-web:${VERSION}

up:
	@echo "🚀 Booting Docker stack..."
	docker compose up -d --remove-orphans

down:
	@echo "⛔ Shutting down Docker stack..."
	docker compose down

restart: reset
reset: reset-worker reset-app reset-npm-dev

reset-worker:
	@echo "🔄 Resetting worker..."
	docker compose restart worker

reset-app:
	@echo "🔄 Resetting app..."
	docker compose restart app

reset-npm-dev:
	@echo "🔄 Resetting npm-dev..."
	docker compose restart npm-dev

init: composer-install create-database create-schema load-fixtures

composer-install:
	@echo "📦 Installing composer dependencies..."
	docker compose exec -it app composer install

create-database:
	@echo "🗄️  Creating database..."
	docker compose exec -it app bin/console doctrine:database:create --if-not-exists

load-fixtures:
	@echo "📥 Loading fixtures..."
	docker compose exec -it app bin/console doctrine:fixtures:load -q

create-schema:
	@echo "📐 Creating database schema..."
	docker compose exec -it app bin/console doctrine:schema:update --force

init-test: create-test-database create-test-schema

create-test-database:
	@echo "🗄️  Creating test database..."
	docker compose exec -it app bin/console doctrine:database:create --env=test --if-not-exists

create-test-schema:
	@echo "📐 Creating test database schema..."
	docker compose exec -it app bin/console doctrine:schema:update --env=test --force

composer:
	@echo "Run composer"
	docker compose exec -it app composer $(cmd)

shell: shell-backend
shell-backend: backend-shell
backend-shell:
	@echo "💻 Opening shell on app container..."
	docker compose exec -it app bash
	
qa: quality
quality:
	@echo "✅ Running quality checks..."
	docker compose exec -it app composer qa

sa: phpstan
phpstan:
	@echo "🔍 Running static code analysis..."
	docker compose exec -it app vendor/bin/phpstan analyse --memory-limit=1G

cs: style
style: codestyle
codestyle: code-style
code-style:
	@echo "💄 Fixing code style..."
	docker compose exec -it app vendor/bin/php-cs-fixer fix

test: test-backend test-frontend

backend-test: test-backend
test-backend:
	@echo "🧪 Running backend tests..."
	docker compose exec -it app bin/phpunit

frontend-test: test-frontend
test-frontend:
	@echo "🧪 Running frontend tests..."
	docker compose exec -it npm-dev npm test -- --run
	
arch:
	@echo "🏛️  Testing architecture..."
	docker compose exec -it app vendor/bin/deptrac analyse --report-uncovered

clear:
	@echo "🗑️  Clearing all caches..."
	docker compose exec -it app composer clear

maintenance: maintain
maintain: show-composer-updates update-composer-dependencies update-npm-dependencies

show-composer-updates:
	@echo "📊 Checking for outdated composer packages..."
	docker compose exec -it app composer show --outdated
	
update-composer-dependencies:
	@echo "📦 Updating composer dependencies..."
	docker compose exec -it app composer update -W

update-npm-dependencies:
	@echo "📦 Updating npm dependencies..."
	docker compose exec -it npm-dev npm update --save

coverage:
	@echo "📈 Generating coverage report..."
	docker compose exec -it app bin/phpunit -c phpunit.xml.dist --coverage-html ./coverage

npm-build:
	@echo "⚛️  Creating frontend build..."
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
