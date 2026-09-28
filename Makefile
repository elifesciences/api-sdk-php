PROJECT_NAME = api-sdk-php
PHP_VERSION = 8.5

.PHONY: clean
clean:
	rm -rf vendor
	rm -f composer.lock

vendor:
	composer install

.PHONY: test
test: vendor
	./project_tests.sh

.PHONY: build
build:
	$(if $(PHP_VERSION),,$(error PHP_VERSION make variable needs to be set))
	docker buildx build --load --build-arg=PHP_VERSION=$(PHP_VERSION) -t $(PROJECT_NAME):$(PHP_VERSION) .

.PHONY: lint
lint: build
	docker run --rm $(PROJECT_NAME):$(PHP_VERSION) bash -c 'vendor/bin/phpcs --standard=phpcs.xml.dist --warning-severity=0 -p src/ scripts/ test/ spec/'

.PHONY: lint-fix
lint-fix:
	vendor/bin/phpcbf --standard=phpcs.xml.dist --warning-severity=0 -p src/ scripts/ test/ spec/

.PHONY: test-ci
test-ci: build lint
	docker run --rm $(PROJECT_NAME):$(PHP_VERSION) bash -c './project_tests.sh'

.PHONY: test-ci-8.4
test-ci-8.4:
	@$(MAKE) PHP_VERSION=8.4 test-ci
.PHONY: test-ci-8.5
test-ci-8.5:
	@$(MAKE) PHP_VERSION=8.5 test-ci

.PHONY: test-ci-all
test-ci-all: test-ci-8.4 test-ci-8.5
