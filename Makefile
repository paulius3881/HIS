.PHONY: default
default: help


.PHONY: help
help: ## Get this help.
	@echo Tasks:
	@awk 'BEGIN {FS = ":.*?## "} /^[a-zA-Z_-]+:.*?## / {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}' $(MAKEFILE_LIST)

FORCE:

.PHONY:
build:
	@cd docker && \
	docker-compose build


.PHONY:
run: ## Start docker containers
	@cd docker && \
	docker-compose up -d && \
	docker run -p 8000 -e BASE_URL=/swagger -e SWAGGER_JSON=./swagger/swagger.json -v /bar:/foo swaggerapi/swagger-ui

.PHONY:
stop: ## Stop docker containers
	@cd docker && docker-compose down

.PHONY:
ssh: ## Connect to docker containers
	@cd docker && docker-compose exec webserver bash