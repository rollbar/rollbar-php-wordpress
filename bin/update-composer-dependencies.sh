#!/usr/bin/env bash

# Print Commands
set -x

# Exit on error
set -e

# Change to the parent directory of this script
cd "$(dirname "$(dirname "$(readlink -fm "$0")")")"

# restart the container in PHP 7
WP_ENV_PHP_VERSION="7.0" npm run wp-env start -- --update

# install composer dependencies
WP_ENV_PHP_VERSION="7.0" npm run wp-env -- run --env-cwd='wp-content/plugins/rollbar-php-wordpress/php7' tests-cli composer update

# restart the container in PHP 8.0
WP_ENV_PHP_VERSION="8.0" npm run wp-env start -- --update

# install composer dependencies
WP_ENV_PHP_VERSION="8.0" npm run wp-env -- run --env-cwd='wp-content/plugins/rollbar-php-wordpress/php8' tests-cli composer update
