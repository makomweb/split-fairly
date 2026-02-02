# Copilot instructions for this repository

Preferred behavior for running backend tests and quality checks:

- Always run backend tests and QA inside the docker-compose "app" service so environment, PHP extensions and dependencies match the container.
- Prefer using the Makefile targets which already invoke the app service. Examples:
  - make test        # runs phpunit inside the app container
  - make quality     # runs composer qa inside the app container

If running commands directly inside the container, run non-interactively (no -it) to avoid TTY allocation issues:

- docker compose exec -T app bin/phpunit
- docker compose exec -T app composer qa
- docker compose exec -T app vendor/bin/phpstan analyse --memory-limit=1G
- docker compose exec -T app vendor/bin/php-cs-fixer fix
- docker compose exec -T app vendor/bin/deptrac analyse --report-uncovered

Guidelines for Copilot CLI usage in this repo:

- When asked to run tests or QA, prefer the Makefile targets (make test, make quality) executed in the repository root.
- If running inside a container is required, always target the "app" service and use `-T` for exec to run non-interactively.
- When proposing automation or CI commands, include explicit container commands using the "app" service so they work in the project's docker-compose setup.

These instructions ensure consistent, reproducible test and QA runs by using the project's containerized environment.
