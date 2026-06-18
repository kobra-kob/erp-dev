#!/usr/bin/env bash
#
# ArtisanFlow ERP — mise à jour vers une nouvelle version (Debian bare-metal).
# À lancer APRÈS une première installation faite avec install.sh.
# Préserve le .env, la base, les fichiers téléversés (storage) et fait une
# sauvegarde avant tout. Migrations ADDITIVES uniquement (jamais migrate:fresh).
#
# Utilisation (en root, depuis le dépôt mis à jour — dossier src/ présent) :
#   sudo bash deploy/update.sh
#
# Variable optionnelle : APP_DIR (défaut /var/www/artisanflow)
#
set -euo pipefail

APP_DIR="${APP_DIR:-/var/www/artisanflow}"
BACKUP_DIR="${BACKUP_DIR:-/var/backups/artisanflow}"

log() { echo -e "\n\033[1;34m==> $*\033[0m"; }

[ "$(id -u)" -eq 0 ] || { echo "Ce script doit être lancé en root (sudo)."; exit 1; }

SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
SRC_DIR="$(dirname "$SCRIPT_DIR")/src"
[ -f "$SRC_DIR/artisan" ] || { echo "Nouvelle version introuvable dans $SRC_DIR."; exit 1; }
[ -f "$APP_DIR/.env" ]    || { echo "$APP_DIR n'est pas une installation existante (.env manquant). Utilisez install.sh."; exit 1; }

# ----------------------------- Sauvegarde ------------------------------
log "Sauvegarde de la base et du .env"
TS="$(date +%Y%m%d-%H%M%S)"
mkdir -p "$BACKUP_DIR"
DB_DATABASE="$(grep -E '^DB_DATABASE=' "$APP_DIR/.env" | cut -d= -f2-)"
DB_USERNAME="$(grep -E '^DB_USERNAME=' "$APP_DIR/.env" | cut -d= -f2-)"
DB_PASSWORD="$(grep -E '^DB_PASSWORD=' "$APP_DIR/.env" | cut -d= -f2-)"
if mysqldump -u"$DB_USERNAME" -p"$DB_PASSWORD" "$DB_DATABASE" > "$BACKUP_DIR/db-$TS.sql" 2>/dev/null; then
    echo "  base sauvegardée : $BACKUP_DIR/db-$TS.sql"
else
    echo "  ⚠ sauvegarde base échouée — vérifiez les identifiants DB avant de continuer."; exit 1
fi
cp "$APP_DIR/.env" "$BACKUP_DIR/env-$TS"

# ----------------------------- Mise à jour -----------------------------
cd "$APP_DIR"

log "Mode maintenance ON"
php artisan down --retry=15 || true

log "Copie du nouveau code (préserve .env, storage, vendor, uploads)"
rsync -a --delete \
    --exclude '.env' \
    --exclude '/storage' \
    --exclude '/vendor' \
    --exclude 'node_modules' \
    --exclude '/bootstrap/cache' \
    "$SRC_DIR"/ "$APP_DIR"/

log "Dépendances PHP (production)"
composer install --no-dev --optimize-autoloader --no-interaction

log "Migrations (additives — aucune perte de données)"
php artisan migrate --force

log "Régénération des caches"
php artisan optimize:clear
php artisan optimize
php artisan view:cache
php artisan event:cache

log "Permissions (www-data)"
chown -R www-data:www-data "$APP_DIR"
chmod -R ug+rwX "$APP_DIR/storage" "$APP_DIR/bootstrap/cache"

log "Mode maintenance OFF"
php artisan up

cat <<DONE

============================================================
  ArtisanFlow ERP — mise à jour terminée ✅
============================================================
  Application .... $APP_DIR
  Sauvegarde DB .. $BACKUP_DIR/db-$TS.sql
  Sauvegarde env . $BACKUP_DIR/env-$TS
  En cas de problème : restaurez avec
    mysql -u$DB_USERNAME -p $DB_DATABASE < $BACKUP_DIR/db-$TS.sql
============================================================
DONE
