#!/usr/bin/env bash
#
# ArtisanFlow ERP — provisioning & déploiement sur Debian 12 (bookworm)
# LAMP : Apache + PHP 8.4 (dépôt Sury) + MariaDB.
#
# Usage (en root) :
#   1) Déposer le code dans $APP_DIR (git clone ou rsync du dossier src/).
#   2) Adapter les variables ci-dessous.
#   3) bash deploy.sh provision   # 1ère fois : installe LAMP + dépendances
#      bash deploy.sh deploy      # à chaque mise à jour de code
#
set -euo pipefail

# ----------------------- Variables à adapter -----------------------
APP_DIR="/var/www/artisanflow"          # racine de l'app Laravel (contient public/)
DOMAIN="artisanflow.example.com"
DB_NAME="artisanflow"
DB_USER="artisanflow"
DB_PASS="CHANGEZ_MOI_mot_de_passe_fort"
LE_EMAIL="admin@artisanflow.example.com" # e-mail Let's Encrypt
PHP_V="8.4"
# -------------------------------------------------------------------

log() { echo -e "\n\033[1;34m==> $*\033[0m"; }

provision() {
    log "Mise à jour du système"
    apt-get update && apt-get upgrade -y

    log "Dépôt Sury pour PHP ${PHP_V} (Debian ne fournit que 8.2)"
    apt-get install -y apt-transport-https lsb-release ca-certificates curl gnupg2
    curl -sSL https://packages.sury.org/php/apt.gpg -o /usr/share/keyrings/sury-php.gpg
    echo "deb [signed-by=/usr/share/keyrings/sury-php.gpg] https://packages.sury.org/php/ $(lsb_release -sc) main" \
        > /etc/apt/sources.list.d/sury-php.list
    apt-get update

    log "Installation Apache + PHP ${PHP_V} + MariaDB"
    apt-get install -y \
        apache2 mariadb-server \
        php${PHP_V} libapache2-mod-php${PHP_V} \
        php${PHP_V}-cli php${PHP_V}-mysql php${PHP_V}-mbstring php${PHP_V}-xml \
        php${PHP_V}-curl php${PHP_V}-zip php${PHP_V}-gd php${PHP_V}-intl php${PHP_V}-bcmath \
        unzip git

    log "Composer"
    if ! command -v composer >/dev/null; then
        curl -sS https://getcomposer.org/installer | php
        mv composer.phar /usr/local/bin/composer
    fi

    log "Modules Apache (rewrite, ssl, headers, deflate, expires)"
    a2enmod rewrite ssl headers deflate expires
    a2dissite 000-default.conf || true

    log "Base de données MariaDB"
    mysql <<SQL
CREATE DATABASE IF NOT EXISTS ${DB_NAME} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASS}';
GRANT ALL PRIVILEGES ON ${DB_NAME}.* TO '${DB_USER}'@'localhost';
FLUSH PRIVILEGES;
SQL

    log "VirtualHost Apache"
    cp "$(dirname "$0")/apache-artisanflow.conf" /etc/apache2/sites-available/artisanflow.conf
    sed -i "s/artisanflow.example.com/${DOMAIN}/g" /etc/apache2/sites-available/artisanflow.conf
    a2ensite artisanflow.conf

    log "Certificat HTTPS (Let's Encrypt / Certbot)"
    apt-get install -y certbot python3-certbot-apache
    certbot --apache -d "${DOMAIN}" --non-interactive --agree-tos -m "${LE_EMAIL}" --redirect || \
        log "Certbot a échoué (DNS/ports ?). Configurez le TLS manuellement plus tard."

    log "Planificateur (cron) : relances de factures + tâches planifiées"
    echo "* * * * * www-data cd ${APP_DIR} && php artisan schedule:run >> /dev/null 2>&1" \
        > /etc/cron.d/artisanflow-scheduler

    systemctl reload apache2
    log "Provisioning terminé. Lancez maintenant : bash deploy.sh deploy"
}

deploy() {
    cd "${APP_DIR}"

    log "Dépendances PHP (sans dev, autoloader optimisé)"
    composer install --no-dev --optimize-autoloader --no-interaction

    if [ ! -f .env ]; then
        log ".env absent → copie du gabarit de production"
        cp "$(dirname "$0")/.env.production.example" .env
        php artisan key:generate
        echo "!! Éditez ${APP_DIR}/.env (DB, mot de passe, domaine, SMTP) puis relancez deploy."
        exit 1
    fi

    log "Migrations (production)"
    php artisan migrate --force

    log "Mise en cache (config/routes/vues/events)"
    php artisan optimize        # config + routes
    php artisan view:cache
    php artisan event:cache

    log "Permissions (Apache = www-data)"
    chown -R www-data:www-data "${APP_DIR}"
    chmod -R 755 "${APP_DIR}"
    chmod -R 775 "${APP_DIR}/storage" "${APP_DIR}/bootstrap/cache"

    systemctl reload apache2
    log "Déploiement terminé : https://${DOMAIN}"
}

case "${1:-}" in
    provision) provision ;;
    deploy)    deploy ;;
    *) echo "Usage: bash deploy.sh {provision|deploy}"; exit 1 ;;
esac
