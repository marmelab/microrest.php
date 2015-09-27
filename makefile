.PHONY: test

install-demo:
	composer -d=examples/ng-admin install
	bower --config.cwd=./examples/ng-admin/web/admin install
	cp examples/ng-admin/app.db-dist examples/ng-admin/app.db

demo-ng-admin:
	composer -d=examples/ng-admin run

demo-stack:
    composer -d=examples/stack run

install:
	composer install --dev --prefer-source

test:
	composer test
