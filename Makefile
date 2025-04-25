PROJECT_NAME = api-sdk-php
PHP_VERSION = 7.1

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

.PHONY: test-ci-7.1
test-ci-7.1:
	@$(MAKE) PHP_VERSION=7.1 test-ci
.PHONY: test-ci-7.2
test-ci-7.2:
	@$(MAKE) PHP_VERSION=7.2 test-ci
.PHONY: test-ci-7.3
test-ci-7.3:
	@$(MAKE) PHP_VERSION=7.3 test-ci
.PHONY: test-ci-7.4
test-ci-7.4:
	@$(MAKE) PHP_VERSION=7.4 test-ci
.PHONY: test-ci-8.0
test-ci-8.0:
	@$(MAKE) PHP_VERSION=8.0 test-ci
.PHONY: test-ci-8.1
test-ci-8.1:
	@$(MAKE) PHP_VERSION=8.1 test-ci
.PHONY: test-ci-8.2
test-ci-8.2:
	@$(MAKE) PHP_VERSION=8.2 test-ci
.PHONY: test-ci-8.3
test-ci-8.3:
	@$(MAKE) PHP_VERSION=8.3 test-ci

.PHONY: test-ci-all
test-ci-all: test-ci-7.1 test-ci-7.2 test-ci-7.3 test-ci-7.4 test-ci-8.0 test-ci-8.1 test-ci-8.2 test-ci-8.3
