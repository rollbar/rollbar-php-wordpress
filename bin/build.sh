#! /bin/sh

set -e

echo "Building plugin ..."

ROOT_DIR=$(realpath "$(dirname "$(dirname "$0")")")

rm -rf "$ROOT_DIR/build"
mkdir -p "$ROOT_DIR/build"

echo " - Copying files to build/ ..."

cp -r "$ROOT_DIR/mu-plugin/" "$ROOT_DIR/build/mu-plugin/"
cp -r "$ROOT_DIR/public/" "$ROOT_DIR/build/public/"
cp -r "$ROOT_DIR/src/" "$ROOT_DIR/build/src/"
cp -r "$ROOT_DIR/templates/" "$ROOT_DIR/build/templates/"
cp "$ROOT_DIR/composer.json" "$ROOT_DIR/build/"
cp "$ROOT_DIR/composer.lock" "$ROOT_DIR/build/"
cp "$ROOT_DIR/LICENSE" "$ROOT_DIR/build/"
cp "$ROOT_DIR/README.md" "$ROOT_DIR/build/"
cp "$ROOT_DIR/readme.txt" "$ROOT_DIR/build/"
cp "$ROOT_DIR/rollbar.php" "$ROOT_DIR/build/"

echo " - Installing dependencies with Composer ..."

cd "$ROOT_DIR/build"

composer install --no-dev --optimize-autoloader

echo " - Namespace prefixing with PHP-Scoper ..."

cd "$ROOT_DIR"

rm -rf "$ROOT_DIR/dist"
vendor/bin/php-scoper add-prefix "$ROOT_DIR/build"
