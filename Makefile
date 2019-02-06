# Tutorial: http://www.cs.colby.edu/maxwell/courses/tutorials/maketutor/
# Docs: https://www.gnu.org/software/make/

# Help
.PHONY: help

help:
	$(info Run `make setup` to configure the Git Hooks and install the dependencies`)
	$(info Run `make install` to install the dependencies)
	$(info Run `make install-prod` to install the dependencies in production mode)
	$(info Run `make tests` to run all tests)

# Setup
.PHONY: setup githooks

setup:: githooks
setup:: install

githooks:
ifndef CI
	@find .git/hooks -type l -exec rm {} \;
	@find .githooks -type f -exec ln -sf ../../{} .git/hooks/ \;
	$(info Git Hooks installed)
else
	$(info Skipping Git Hooks in CI)
endif

# Install
.PHONY: install

install: composer-install

install-prod: composer-install-prod

# Tests
.PHONY: tests

tests:: phpunit

# Git Hooks
.PHONY: precommit

precommit:: validate-composer
precommit:: dupes
precommit:: compatibility

# precommit
.PHONY: dupes compatibility validate-composer

dupes: composer-install
	./.make/check-duplicates.sh

compatibility: composer-install
	./.make/check-compatibility.sh

validate-composer: composer-install
	./.make/check-composer.sh

# Dependency managers

## Composer
.PHONY: composer-install

composer.lock: composer-install
	@touch $@

vendor/autoload.php: composer-install
	@touch $@

composer-install:
	$(info Installing Composer dependencies)
	@composer install

composer-install-prod:
	$(info Installing Composer dependencies)
	@composer --no-dev install

# Tests
.PHONY: phpunit

phpunit: composer-install
	$(info Running PhpUnit)
	@vendor/bin/phpunit --fail-on-warning
