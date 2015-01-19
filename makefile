.PHONY: test

install-demo:
	composer -d=examples/ng-admin install
	bower --config.cwd=./examples/ng-admin/web/admin install
	cp examples/ng-admin/app.db-dist examples/ng-admin/app.db

run-demo:
	composer -d=examples/ng-admin run

install:
	composer install --dev --prefer-source

test:
	composer test
