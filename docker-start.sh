#!/bin/bash
set -e

echo "==> Clearing config..."
php -d memory_limit=256M artisan config:clear

echo "==> Running migrations..."
php -d memory_limit=256M artisan migrate --force

echo "==> Seeding database (if needed)..."
php -d memory_limit=256M artisan db:seed --force 2>&1 || echo "Seeding skipped or already done."

echo "==> Starting server..."
exec php -d memory_limit=256M artisan serve --host=0.0.0.0 --port=10000
