# Déploiement — ArtisanFlow ERP (Debian LAMP + HTTPS)

Guide de mise en production sur un serveur **Debian 12 (bookworm)** en **DMZ**,
derrière un pare-feu **pfSense**, supervisé par **Zabbix**.

Fichiers fournis dans `deploy/` :

| Fichier | Rôle |
|---------|------|
| `install.sh` | **Installation initiale tout-en-un** (recommandé) : LAMP + MariaDB + app + admin, en une commande |
| `update.sh` | **Mise à jour** vers une nouvelle version (sauvegarde + migrations additives, sans perte de données) |
| `deploy.sh` | Variante en 2 temps (`provision` puis `deploy`) avec édition manuelle du `.env` |
| `apache-artisanflow.conf` | VirtualHost Apache (HTTP→HTTPS, TLS, en-têtes sécurité) |
| `.env.production.example` | Gabarit de configuration de production |
| `zabbix/artisanflow.conf` | Métriques applicatives pour zabbix-agent2 |
| `pfsense-firewall.md` | Règles NAT / pare-feu pfSense (DMZ) |

## Architecture cible

```
Internet ─▶ pfSense (DMZ) ─▶ Debian : Apache 2 ─▶ PHP 8.4 (mod_php) ─▶ Laravel 13
                                         └────────▶ MariaDB 10.11+
                                Zabbix-agent2 ─▶ Serveur Zabbix (LAN)
```

> ⚠️ **PHP 8.4 obligatoire** (Laravel 13 / Symfony 8). Debian 12 ne fournit que
> PHP 8.2 → le script ajoute le dépôt **Sury** (`packages.sury.org`).

---

## 0. Installation express (tout-en-un)

Sur un serveur **Debian 12 fraîchement installé**, en root, depuis le dépôt
(le dossier `src/` doit être présent) :

```bash
sudo bash deploy/install.sh
```

Le script installe et configure **tout automatiquement** (Apache, PHP 8.4, MariaDB
locale, dépendances, base, `.env`, clé, migrations, caches, permissions, vhost,
cron, pare-feu), **génère les mots de passe** et crée un **compte administrateur**.
À la fin, il affiche un récapitulatif : URL, port HTTP, identifiants admin et
accès base de données.

Personnalisation possible via variables d'environnement :

```bash
sudo APP_DOMAIN=erp.mondomaine.fr HTTP_PORT=80 ADMIN_EMAIL=patron@mondomaine.fr \
     COMPANY_NAME="Plomberie Dupont" bash deploy/install.sh
# SEED_DEMO=1 pour charger des données de démonstration
```

> Le reste de ce document détaille l'installation **manuelle / pas-à-pas** (utile
> pour comprendre, personnaliser ou dépanner) et l'exploitation (HTTPS, Zabbix, pfSense).

## 1. Pré-requis
- Serveur Debian 12, accès **root** (ou sudo).
- Un **nom de domaine** pointant (DNS A) vers l'IP publique (pour Let's Encrypt).
- Ports **80 et 443** redirigés vers le serveur via pfSense (voir `pfsense-firewall.md`).

## 2. Récupérer le code
```bash
sudo mkdir -p /var/www/artisanflow
# Option git :
sudo git clone <votre-repo> /tmp/artisanflow && sudo cp -r /tmp/artisanflow/src/. /var/www/artisanflow/
# Option rsync depuis le poste de dev (contenu du dossier src/) :
# rsync -av --exclude vendor --exclude node_modules ./src/ root@serveur:/var/www/artisanflow/
```
> On déploie le **contenu de `src/`** (l'application Laravel), pas le dossier Docker.

## 3. Provisioning (1ʳᵉ fois)
Adapter les variables en tête de `deploy.sh` (domaine, DB, e-mail), puis :
```bash
cd /var/www/artisanflow   # ou le dossier contenant deploy.sh
sudo bash deploy.sh provision
```
Ce script installe Apache + PHP 8.4 + MariaDB, crée la base, active le VirtualHost,
obtient le certificat HTTPS (Certbot) et installe le cron du planificateur.

## 4. Déploiement de l'application
```bash
sudo bash deploy.sh deploy
```
Au 1ᵉʳ lancement il copie `.env.production.example` → `.env` et génère la clé ;
**éditez `/var/www/artisanflow/.env`** (mot de passe DB, domaine, SMTP) puis relancez
`sudo bash deploy.sh deploy`. Le script exécute alors : `composer install --no-dev`,
`migrate --force`, mise en cache (`optimize` + `view:cache` + `event:cache`) et
fixe les permissions `www-data`.

Créer le premier compte : ouvrir `https://votre-domaine` → **inscription** (crée
l'entreprise + l'administrateur). Pour des données de démonstration : `php artisan db:seed --force`.

## 5. HTTPS et renouvellement
Certbot installe un timer systemd de renouvellement automatique. Vérifier :
```bash
sudo systemctl status certbot.timer
sudo certbot renew --dry-run
```

## 6. Tâches planifiées & file d'attente
- **Planificateur** (relances de factures) : le cron `/etc/cron.d/artisanflow-scheduler`
  appelle `schedule:run` chaque minute. Vérifier : `php artisan schedule:list`.
- **File d'attente** (optionnel, si vous passez des jobs en file) : créer un service
  systemd exécutant `php artisan queue:work --tries=3` et l'activer.

## 7. Durcissement sécurité
- `APP_DEBUG=false`, `APP_ENV=production` (déjà dans le gabarit).
- **MariaDB** : `sudo mysql_secure_installation`.
- **Pare-feu local** (complément de pfSense) :
  ```bash
  sudo apt install ufw
  sudo ufw allow 80,443/tcp
  sudo ufw allow from <reseau_admin> to any port 22 proto tcp
  sudo ufw enable
  ```
- **fail2ban** (anti brute-force SSH/HTTP) : `sudo apt install fail2ban`.
- En place côté application : CSRF, hash bcrypt, requêtes préparées (Eloquent),
  throttling de connexion (6/min), 2FA TOTP, journal d'audit, en-têtes de sécurité
  (middleware + vhost : HSTS, X-Frame-Options, nosniff).
- Permissions : seuls `storage/` et `bootstrap/cache/` sont inscriptibles par `www-data`.

## 8. Supervision Zabbix
1. Installer l'agent : `sudo apt install zabbix-agent2`, renseigner `Server=<ip_zabbix>`
   dans `/etc/zabbix/zabbix_agent2.conf`.
2. Copier `deploy/zabbix/artisanflow.conf` dans `/etc/zabbix/zabbix_agent2.d/` puis
   `sudo systemctl restart zabbix-agent2`.
3. Sur le serveur Zabbix, lier à l'hôte les templates :
   - **Linux by Zabbix agent** → CPU, RAM, **disque**, charge.
   - **Apache by Zabbix agent** → activer `mod_status` (`a2enmod status`) avec un
     `/server-status` restreint à `127.0.0.1`.
   - **MySQL/MariaDB by Zabbix agent 2** → créer un user de monitoring en lecture.
4. Métriques applicatives ajoutées (clés) : `artisanflow.health` (code HTTP de `/up`,
   alerte si ≠ 200), `artisanflow.invoices.unpaid`, `artisanflow.queue.size`.
   Créer un déclencheur : `last(/HOST/artisanflow.health)<>200` → **service down**.

## 9. Pare-feu pfSense
Voir **`pfsense-firewall.md`** : interface DMZ, NAT 80/443, blocage DMZ→LAN,
ouverture sortante (apt, ACME, SMTP, DNS, Zabbix).

## 10. Mises à jour ultérieures (nouvelles versions)

> **Important :** ne **jamais** relancer `install.sh` pour une mise à jour — il
> réécrit le `.env` et régénère les mots de passe. Pour mettre à jour, utiliser
> **`update.sh`**.

Sur le serveur, récupérer la nouvelle version du dépôt puis lancer le script :
```bash
cd /chemin/vers/le-depot      # le dossier contenant src/ et deploy/
git pull                      # (ou rsync/scp de la nouvelle version)
sudo bash deploy/update.sh
```
`update.sh` enchaîne automatiquement, **sans perte de données** :
1. **Sauvegarde** de la base (`mysqldump`) et du `.env` dans `/var/backups/artisanflow/` ;
2. **mode maintenance** (`php artisan down`) ;
3. copie du nouveau code en **préservant** `.env`, `storage/` (fichiers téléversés)
   et `vendor/` ;
4. `composer install --no-dev` ;
5. **`php artisan migrate --force`** — migrations **additives uniquement**
   (jamais `migrate:fresh`, qui effacerait les données) ;
6. purge + régénération des caches (`optimize`, `view:cache`, `event:cache`) ;
7. permissions `www-data` ;
8. **fin du mode maintenance** (`php artisan up`).

En cas de souci, restaurer la base depuis la sauvegarde affichée à la fin :
```bash
mysql -u<DB_USER> -p <DB_NAME> < /var/backups/artisanflow/db-<horodatage>.sql
```

## Checklist de mise en production
- [ ] DNS A → IP publique ; NAT pfSense 80/443 OK
- [ ] `deploy.sh provision` puis `deploy` exécutés sans erreur
- [ ] `.env` : `APP_DEBUG=false`, DB, domaine, SMTP renseignés
- [ ] HTTPS valide (cadenas) + `certbot renew --dry-run` OK
- [ ] `php artisan schedule:list` montre `invoices:send-reminders`
- [ ] Agent Zabbix remonte CPU/RAM/disque/Apache/MariaDB + `artisanflow.health`
- [ ] Règle pfSense **DMZ→LAN bloquée** vérifiée
- [ ] `mysql_secure_installation` + `ufw` + `fail2ban` actifs

---

### Alternative : déploiement Docker
Le `docker-compose.yml` du dépôt peut aussi tourner sur le serveur Debian
(`apt install docker.io docker-compose-plugin`) — pratique mais plus éloigné de la
cible « LAMP classique » du cahier des charges. Dans ce cas, placer un reverse-proxy
TLS (Caddy/Traefik/Nginx + Certbot) devant le conteneur `app`.
