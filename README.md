# ArtisanFlow ERP

ERP SaaS modulaire pour artisans et petites entreprises (plomberie, électricité,
menuiserie, bâtiment, réparation…). Inspiré d'Odoo mais plus simple, plus rapide
et spécialisé métiers artisanaux.

## Stack technique

| Couche      | Techno                                  |
|-------------|-----------------------------------------|
| Backend     | PHP 8.4 / Laravel 13                     |
| Base        | MariaDB 11                              |
| Frontend    | Bootstrap 5 + JavaScript (Blade)        |
| Serveur web | Apache 2 (mod_rewrite)                  |
| Conteneurs  | Docker / docker-compose                 |

## Architecture (multi-entreprises)

```
Utilisateur ─< appartient à >─ Entreprise (company)
                                   │
                                   ├─ Employés (users)
                                   └─ Données métier (clients, devis, factures…)
```

Toutes les données métier sont **isolées par entreprise** via une colonne
`company_id` et un *global scope* Eloquent (`BelongsToCompany`).

## Démarrage rapide

Pré-requis : Docker Desktop.

```powershell
# 1. Construire et démarrer la stack
docker compose up -d --build

# 2. Installer/mettre à jour les dépendances PHP (si besoin)
docker compose exec app composer install

# 3. Générer la clé applicative + migrer la base
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --seed
```

| Service       | URL                      |
|---------------|--------------------------|
| Application   | http://localhost:8080    |
| phpMyAdmin    | http://localhost:8081    |
| MariaDB       | localhost:3306           |

Identifiants base : `artisanflow` / `secret` (root : `rootsecret`).

## Performances (important sous Windows / OneDrive)

Le bind-mount du code depuis Windows (surtout un dossier **OneDrive**) rend les
lectures de fichiers très lentes : sans optimisation, une page mettait **~9,5 s**.
Après optimisation : **~0,1–0,3 s** par page. Mesures appliquées :

1. **Volumes Docker natifs** (FS Linux) pour les dossiers à forte I/O, au lieu du
   bind-mount : `vendor`, `storage/framework`, `bootstrap/cache` (voir
   `docker-compose.yml`). C'est le gain principal (×50).
2. **OPcache** + `realpath_cache` réglés dans `docker/php/php.ini`.
3. **Caches Laravel** : routes, vues et events (le cache de **config est laissé
   désactivé** pour que le `.env` reste pris en compte et pour protéger la base de test).

Après un `docker compose down -v` (qui supprime les volumes), réinstaller vendor :

```powershell
docker compose up -d --build
docker compose exec app composer install --optimize-autoloader
docker compose exec app php artisan migrate:fresh --seed
docker compose exec app php artisan route:cache
docker compose exec app php artisan view:cache
```

> ⚠️ Les **routes** étant mises en cache, après toute modification de `routes/*.php`
> lancez `docker compose exec app php artisan optimize:clear` (puis re-cachez si besoin).
> Les vues, elles, se recompilent automatiquement à chaque modification.
>
> 💡 Pour des performances encore meilleures et éviter les conflits de synchro,
> il est recommandé de déplacer le projet **hors de OneDrive**.

## Rôles

| Rôle      | Périmètre                                  |
|-----------|--------------------------------------------|
| `ADMIN`   | Gère tout (entreprise, utilisateurs, data) |
| `GERANT`  | Clients, devis, factures, planning         |
| `EMPLOYE` | Planning, chantiers qui lui sont assignés  |

## Roadmap

- **Phase 1** ✅ : Auth + rôles + 2FA + Dashboard launcher + Clients (CRM)
- **Phase 2** ✅ : Devis + Factures + paiements + génération PDF
- **Phase 3** ✅ : Planning (calendrier) + Chantiers (avancement, pièces jointes) + Stock (alertes)
- **Phase 4** ✅ : Assistant Artisan (IA) + Statistiques (graphiques) + durcissement sécurité

## Assistant Artisan (IA)

L'assistant fonctionne **gratuitement sans configuration** : il répond aux questions
métier (impayés, CA, stock, devis…) en interrogeant directement la base de données.

Pour des réponses formulées par une IA, ajoutez une clé d'API **compatible OpenAI**
dans `src/.env` (voir `config/assistant.php`). Fournisseur gratuit recommandé :
[Groq](https://console.groq.com) (clé gratuite, format OpenAI). Variables :

```dotenv
ASSISTANT_API_KEY=gsk_votre_cle
ASSISTANT_BASE_URL=https://api.groq.com/openai/v1
ASSISTANT_MODEL=llama-3.3-70b-versatile
```

Compatible aussi avec OpenRouter (modèles `:free`), OpenAI (payant) ou Ollama (local).

## Déploiement (production)

Mise en production sur **Debian 12 LAMP + HTTPS**, en DMZ derrière **pfSense**,
supervisée par **Zabbix** — voir **[deploy/DEPLOYMENT.md](deploy/DEPLOYMENT.md)**.
En résumé :

```bash
sudo bash deploy/deploy.sh provision   # LAMP + PHP 8.4 (dépôt Sury) + MariaDB + Certbot
sudo bash deploy/deploy.sh deploy      # composer --no-dev + migrate + caches + permissions
```

## Structure du dépôt

```
APP-ERP/
├── docker-compose.yml      # Orchestration app + db + phpmyadmin (dev)
├── docker/                 # Image PHP 8.4 + Apache, php.ini, entrypoint, vhost
├── deploy/                 # Production : guide, vhost HTTPS, script, Zabbix, pfSense
│   ├── DEPLOYMENT.md
│   ├── deploy.sh
│   ├── apache-artisanflow.conf
│   ├── .env.production.example
│   ├── zabbix/artisanflow.conf
│   └── pfsense-firewall.md
└── src/                    # Application Laravel
```
