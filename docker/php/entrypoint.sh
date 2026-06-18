#!/bin/sh
set -e

# storage/framework et bootstrap/cache sont des volumes Docker (FS Linux rapide)
# qui démarrent vides : on (re)crée l'arborescence attendue par Laravel.
mkdir -p \
    /var/www/html/storage/framework/cache/data \
    /var/www/html/storage/framework/sessions \
    /var/www/html/storage/framework/views \
    /var/www/html/storage/framework/testing \
    /var/www/html/bootstrap/cache

# Permissions d'écriture pour Apache (le bind-mount Windows monte en lecture seule pour www-data).
chmod -R ug+rwX,o+rwX \
    /var/www/html/storage \
    /var/www/html/bootstrap/cache 2>/dev/null || true

exec "$@"
