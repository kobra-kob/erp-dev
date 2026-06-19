#!/usr/bin/env bash
#
# ArtisanFlow ERP — installeur TOUT-EN-UN pour Debian 12 (bare-metal).
# Installe et configure automatiquement : Apache + PHP 8.4 + MariaDB (même
# serveur) + l'application + un compte administrateur. Génère les mots de passe
# et affiche un récapitulatif (URL, ports, identifiants) à la fin.
#
# Utilisation (en root, depuis le dépôt — le dossier src/ doit être présent) :
#   sudo bash deploy/install.sh
#
# Variables optionnelles (sinon valeurs automatiques) :
#   APP_DIR  APP_DOMAIN  HTTP_PORT  DB_NAME  DB_USER  ADMIN_EMAIL  COMPANY_NAME  SEED_DEMO=1
#
set -euo pipefail

# ----------------------------- Paramètres -----------------------------
PHP_V=8.4
APP_DIR="${APP_DIR:-/var/www/artisanflow}"
HTTP_PORT="${HTTP_PORT:-80}"
DB_NAME="${DB_NAME:-artisanflow}"
DB_USER="${DB_USER:-artisanflow}"
COMPANY_NAME="${COMPANY_NAME:-Mon entreprise}"
SEED_DEMO="${SEED_DEMO:-0}"

gen() { openssl rand -hex 12; }   # mot de passe 24 caractères hexadécimaux
DB_PASS="${DB_PASS:-$(gen)}"
ADMIN_PASS="$(gen)"

SERVER_IP="$(hostname -I 2>/dev/null | awk '{print $1}')"
APP_DOMAIN="${APP_DOMAIN:-$SERVER_IP}"
ADMIN_EMAIL="${ADMIN_EMAIL:-admin@${APP_DOMAIN}}"

if [ "$HTTP_PORT" = "80" ]; then APP_URL="http://${APP_DOMAIN}"; else APP_URL="http://${APP_DOMAIN}:${HTTP_PORT}"; fi

log() { echo -e "\n\033[1;34m==> $*\033[0m"; }

# ----------------------------- Pré-checks ------------------------------
[ "$(id -u)" -eq 0 ] || { echo "Ce script doit être lancé en root (sudo)."; exit 1; }

SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
SRC_DIR="$(dirname "$SCRIPT_DIR")/src"
[ -f "$SRC_DIR/artisan" ] || { echo "Application introuvable dans $SRC_DIR (lancez depuis le dépôt)."; exit 1; }

# ----------------------------- Installation ----------------------------
log "Mise à jour du système + dépôt Sury (PHP ${PHP_V})"
export DEBIAN_FRONTEND=noninteractive
apt-get update -y && apt-get upgrade -y
apt-get install -y curl gnupg2 ca-certificates lsb-release apt-transport-https unzip git rsync openssl
curl -sSL https://packages.sury.org/php/apt.gpg -o /usr/share/keyrings/sury-php.gpg
echo "deb [signed-by=/usr/share/keyrings/sury-php.gpg] https://packages.sury.org/php/ $(lsb_release -sc) main" \
    > /etc/apt/sources.list.d/sury-php.list
apt-get update -y

log "Apache + PHP ${PHP_V} + MariaDB"
apt-get install -y apache2 mariadb-server \
    "php${PHP_V}" "libapache2-mod-php${PHP_V}" \
    "php${PHP_V}-cli" "php${PHP_V}-mysql" "php${PHP_V}-mbstring" "php${PHP_V}-xml" \
    "php${PHP_V}-curl" "php${PHP_V}-zip" "php${PHP_V}-gd" "php${PHP_V}-intl" "php${PHP_V}-bcmath"

log "Réglages PHP de production (OPcache + JIT + cache de chemins)"
if [ -f "$SCRIPT_DIR/php-production.ini" ]; then
    cp "$SCRIPT_DIR/php-production.ini" "/etc/php/${PHP_V}/apache2/conf.d/99-artisanflow.ini"
fi

log "Composer"
if ! command -v composer >/dev/null; then
    curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
fi

log "Base de données MariaDB (locale)"
systemctl enable --now mariadb
mysql <<SQL
CREATE DATABASE IF NOT EXISTS \`${DB_NAME}\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASS}';
ALTER USER '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASS}';
GRANT ALL PRIVILEGES ON \`${DB_NAME}\`.* TO '${DB_USER}'@'localhost';
FLUSH PRIVILEGES;
SQL

log "Déploiement du code dans ${APP_DIR}"
mkdir -p "$APP_DIR"
rsync -a --exclude vendor --exclude node_modules --exclude .env "$SRC_DIR"/ "$APP_DIR"/
cd "$APP_DIR"

log "Dépendances PHP (production)"
composer install --no-dev --optimize-autoloader --no-interaction

log "Fichier .env"
cat > .env <<ENV
APP_NAME=ArtisanFlow
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=${APP_URL}

APP_LOCALE=fr
APP_FALLBACK_LOCALE=fr
APP_FAKER_LOCALE=fr_FR

LOG_CHANNEL=stack
LOG_STACK=daily
LOG_LEVEL=warning

DB_CONNECTION=mariadb
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=${DB_NAME}
DB_USERNAME=${DB_USER}
DB_PASSWORD=${DB_PASS}

SESSION_DRIVER=file
SESSION_LIFETIME=120
SESSION_SECURE_COOKIE=false
CACHE_STORE=file
QUEUE_CONNECTION=sync

MAIL_MAILER=log
MAIL_FROM_ADDRESS="no-reply@${APP_DOMAIN}"
MAIL_FROM_NAME="\${APP_NAME}"

ASSISTANT_API_KEY=
ASSISTANT_BASE_URL=https://api.groq.com/openai/v1
ASSISTANT_MODEL=llama-3.3-70b-versatile
ENV

log "Clé applicative + migrations"
php artisan key:generate --force
php artisan migrate --force
php artisan storage:link   # photos produits & pièces jointes publiques
if [ "$SEED_DEMO" = "1" ]; then php artisan db:seed --force; fi

log "Compte administrateur (propriétaire)"
php artisan app:create-owner --email="${ADMIN_EMAIL}" --password="${ADMIN_PASS}" --company="${COMPANY_NAME}"

log "Mise en cache (config/routes/vues/events)"
php artisan optimize
php artisan view:cache
php artisan event:cache

log "Permissions (Apache = www-data)"
chown -R www-data:www-data "$APP_DIR"
find "$APP_DIR" -type d -exec chmod 755 {} \;
find "$APP_DIR" -type f -exec chmod 644 {} \;
chmod -R ug+rwX "$APP_DIR/storage" "$APP_DIR/bootstrap/cache"

log "VirtualHost Apache (port ${HTTP_PORT})"
a2enmod rewrite headers >/dev/null
if [ "$HTTP_PORT" != "80" ] && ! grep -q "^Listen ${HTTP_PORT}\b" /etc/apache2/ports.conf; then
    echo "Listen ${HTTP_PORT}" >> /etc/apache2/ports.conf
fi
cat > /etc/apache2/sites-available/artisanflow.conf <<VHOST
<VirtualHost *:${HTTP_PORT}>
    ServerName ${APP_DOMAIN}
    DocumentRoot ${APP_DIR}/public
    <Directory ${APP_DIR}/public>
        AllowOverride All
        Require all granted
        Options -Indexes +FollowSymLinks
    </Directory>
    ErrorLog \${APACHE_LOG_DIR}/artisanflow-error.log
    CustomLog \${APACHE_LOG_DIR}/artisanflow-access.log combined
</VirtualHost>
VHOST
a2dissite 000-default.conf >/dev/null 2>&1 || true
a2ensite artisanflow.conf >/dev/null
systemctl reload apache2

log "Planificateur (relances de factures, etc.)"
echo "* * * * * www-data cd ${APP_DIR} && php artisan schedule:run >> /dev/null 2>&1" \
    > /etc/cron.d/artisanflow-scheduler

log "Pare-feu (ufw)"
apt-get install -y ufw >/dev/null 2>&1 || true
if command -v ufw >/dev/null; then
    ufw allow OpenSSH >/dev/null 2>&1 || ufw allow 22/tcp >/dev/null 2>&1 || true
    ufw allow "${HTTP_PORT}/tcp" >/dev/null 2>&1 || true
    yes | ufw enable >/dev/null 2>&1 || true
fi

# ----------------------------- Récapitulatif ---------------------------
cat <<SUMMARY

============================================================
  ArtisanFlow ERP — installation terminée ✅
============================================================
  APPLICATION
    URL ............ ${APP_URL}
    Port HTTP ...... ${HTTP_PORT}
    Dossier ........ ${APP_DIR}

  CONNEXION ADMINISTRATEUR  (à noter, changez le mot de passe)
    E-mail ......... ${ADMIN_EMAIL}
    Mot de passe ... ${ADMIN_PASS}

  BASE DE DONNÉES  (MariaDB, locale)
    Hôte / Port .... 127.0.0.1 : 3306
    Base ........... ${DB_NAME}
    Utilisateur .... ${DB_USER}
    Mot de passe ... ${DB_PASS}

  Journaux ......... ${APP_DIR}/storage/logs  &  /var/log/apache2
============================================================
  À FAIRE ENSUITE
   - Se connecter et changer le mot de passe administrateur
   - Configurer le SMTP (MAIL_* dans ${APP_DIR}/.env) pour les e-mails
   - Activer HTTPS si domaine public :  apt install certbot python3-certbot-apache && certbot --apache -d ${APP_DOMAIN}
     (puis passer SESSION_SECURE_COOKIE=true dans .env)
============================================================
SUMMARY
