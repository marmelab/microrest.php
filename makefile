.PHONY: test

install:
	composer install

test:
	phpunit
