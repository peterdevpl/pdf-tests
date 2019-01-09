#!/bin/sh

composer install
vendor/bin/phpstan analyse --level=max src tests
vendor/bin/behat

