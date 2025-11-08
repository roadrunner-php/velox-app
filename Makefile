DEV := --env-file .env -f docker-compose.yaml -f docker-compose.dev.yaml

build:
	if [ ! -f ".env" ]; then \
		echo "Creating .env file from .env.example..."; \
		cp .env.example .env; \
		echo "Please update the .env file with your configuration."; \
	fi

	docker compose $(DEV) up --no-start

start:
	docker compose $(DEV) up --remove-orphans -d;

up: build start

pull:
	docker compose $(DEV) pull $(D_SERVICES);

stop:
	docker compose $(DEV) stop;

down:
	docker compose $(DEV) down;

restart:
	docker compose $(DEV) restart;

list:
	docker compose $(DEV) ps;

log-tail:
	docker compose $(DEV) logs --tail=50 -f;

build-app:
	docker compose $(DEV) build vx-app;