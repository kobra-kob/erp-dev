# Expression des besoins — Modules métiers optionnels

**Projet :** ArtisanFlow ERP
**Objet :** Faire évoluer l'ERP vers un **socle commun** complété par des **modules métiers
(verticaux) optionnels**, activables à tout moment par l'entreprise (opticien, immobilier,
concessionnaire, électricien, plombier, peintre…).
**Version :** 1.0 · **Statut :** Expression des besoins (avant conception détaillée)

---

## 1. Contexte et objectif

ArtisanFlow est aujourd'hui un ERP multi-entreprises (multi-tenant) couvrant la gestion
courante : clients, devis, factures, planning, chantiers, stock, dépenses, comptabilité,
congés, assistant IA. Tous les tenants disposent des mêmes modules.

**Objectif** : transformer le produit en **plateforme modulaire** :

- un **socle commun** présent pour tous ;
- un **catalogue de modules métiers** que chaque entreprise **active/désactive à la demande**,
  sans réinstallation ni interruption de service ;
- une **tarification** par module (socle + options).

**Bénéfices attendus** : adresser plusieurs secteurs avec un même produit, augmenter le revenu
moyen par client (montée en gamme), limiter la complexité vue par chaque utilisateur (il ne voit
que ses modules).

---

## 2. Vision fonctionnelle : socle + options

```
┌─────────────────────────────────────────────────────────────┐
│                        SOCLE COMMUN                           │
│  Auth/2FA · Rôles & accès · Clients/CRM · Devis · Factures    │
│  Paiements · Planning · Dépenses · Documents · Comptabilité   │
│  Stock · Chantiers · Statistiques · Assistant IA · Congés     │
└─────────────────────────────────────────────────────────────┘
                    ▲ activation à la demande ▲
┌──────────┬───────────┬──────────────┬───────────┬────────────┐
│ Opticien │ Immobilier│ Concession.  │ Élec/Plomb│  Peintre   │  … (extensible)
└──────────┴───────────┴──────────────┴───────────┴────────────┘
```

> Le socle existe déjà. Les modules métiers **enrichissent** ce socle (catalogues spécialisés,
> entités dédiées, documents réglementaires, écrans spécifiques) plutôt que de le dupliquer.

---

## 3. Le socle commun (existant)

| Domaine | Contenu |
|---------|---------|
| Comptes & sécurité | Inscription (1 owner), connexion, 2FA TOTP, audit, reset mot de passe |
| Rôles & accès | Rôles intégrés + **rôles personnalisés** (modules cochés) |
| Relation client | Clients / CRM, historique |
| Ventes | Devis (validation client en ligne), Factures, paiements, relances, envoi e-mail |
| Exploitation | Planning, Chantiers, Stock (+ réappro auto), Dépenses, Documents |
| Finance | Comptabilité partie double, TVA, bilan, FEC, banque/rapprochement |
| RH | Employés, contrats, Congés |
| Aide | Assistant IA, Statistiques |

---

## 4. Catalogue des modules métiers (besoins par vertical)

> Convention : **BF** = besoin fonctionnel.

### 4.1 Module **Opticien**
Cible : magasins d'optique, opticiens-lunetiers.

| Réf | Besoin |
|-----|--------|
| BF-OPT-1 | Fiche **patient** étendue (n° sécurité sociale, mutuelle, médecin prescripteur) |
| BF-OPT-2 | Saisie d'**ordonnance** : correction OD/OG (sphère, cylindre, axe, addition), écart pupillaire, date, prescripteur |
| BF-OPT-3 | Catalogue spécialisé **montures** et **verres** (traitements, indices) avec stock dédié |
| BF-OPT-4 | **Devis optique** normalisé (mention part Sécu / part mutuelle / reste à charge) |
| BF-OPT-5 | Gestion du **tiers payant** et des **mutuelles** (taux de prise en charge) |
| BF-OPT-6 | Historique visuel du client (évolution des corrections) et rappels de renouvellement |
| BF-OPT-7 | (Optionnel/avancé) Mentions compatibles **télétransmission** SESAM-Vitale (hors périmètre v1) |

### 4.2 Module **Immobilier** (agence / gestion)
Cible : agences immobilières, administrateurs de biens.

| Réf | Besoin |
|-----|--------|
| BF-IMM-1 | Fiche **bien** (type, surface, pièces, DPE, adresse, photos, prix) |
| BF-IMM-2 | **Mandats** (vente / location), propriétaires/bailleurs, statut du mandat |
| BF-IMM-3 | **Locataires**, **baux** (durée, loyer, charges, dépôt de garantie) |
| BF-IMM-4 | **Quittances de loyer** + appels de loyer automatiques (échéancier) |
| BF-IMM-5 | **États des lieux** (entrée/sortie) avec photos |
| BF-IMM-6 | Suivi des **visites** et rapprochement acquéreurs/biens |
| BF-IMM-7 | Calcul et facturation des **honoraires** (vente, location, gestion) |

### 4.3 Module **Concessionnaire** (automobile)
Cible : vente de véhicules neufs/occasion, garages.

| Réf | Besoin |
|-----|--------|
| BF-CON-1 | Fiche **véhicule** (VIN, marque, modèle, immatriculation, km, énergie, état, photos) |
| BF-CON-2 | **Stock véhicules** neuf/occasion avec statut (disponible, réservé, vendu) |
| BF-CON-3 | **Reprises** (estimation, décote) et dossiers de **financement** |
| BF-CON-4 | Suivi des **essais**, réservations, bons de commande véhicule |
| BF-CON-5 | **Garanties** et échéances d'entretien (lien atelier) |
| BF-CON-6 | Documents : bon de commande, certificat de cession, facture véhicule |

### 4.4 Modules **Bâtiment** : Électricien / Plombier / Peintre
Cible : artisans du bâtiment. Le socle (devis, factures, chantiers, planning, stock) couvre
déjà l'essentiel ; ces modules apportent les **spécificités métier**.

| Réf | Besoin (commun bâtiment) |
|-----|--------|
| BF-BAT-1 | **Catalogue de prestations** pré-rempli par métier (bibliothèque de lignes de devis) |
| BF-BAT-2 | **Métrés / quantitatifs** (surfaces, longueurs) injectés dans le devis |
| BF-BAT-3 | **Fiche d'intervention** signée sur place (bon de travaux) |
| BF-BAT-4 | Photos **avant/après** liées au chantier |

| Réf | Spécificités |
|-----|--------------|
| BF-ELEC-1 | Attestations de conformité (**Consuel**), schémas, mentions normatives (NF C 15-100) |
| BF-PLOMB-1 | Certificats (gaz, raccordement), contrats d'entretien chaudière |
| BF-PEINT-1 | Calcul surfaces à peindre (déduction ouvertures), nuanciers/références couleurs, devis au m² |

### 4.5 Extensibilité
Le catalogue doit pouvoir **accueillir de nouveaux métiers** (paysagiste, coiffeur, restauration…)
sans refonte : un module = un paquet déclaratif (voir §8).

---

## 5. Besoins transverses des modules optionnels

| Réf | Besoin |
|-----|--------|
| BF-MOD-1 | **Catalogue / Store de modules** consultable par l'owner (description, prix, captures) |
| BF-MOD-2 | **Activation / désactivation à tout moment**, effet immédiat (pas de redéploiement) |
| BF-MOD-3 | À l'activation : création automatique des données de référence du module ; à la désactivation : **conservation des données** (réactivation sans perte) |
| BF-MOD-4 | Les écrans/menus d'un module n'apparaissent **que s'il est activé** pour le tenant |
| BF-MOD-5 | Intégration avec les **rôles & accès** : les modules activés deviennent cochables par rôle |
| BF-MOD-6 | **Facturation** du module selon l'abonnement (voir §9) ; blocage propre si non payé |
| BF-MOD-7 | Isolation tenant respectée (un module activé chez A n'impacte pas B) |
| BF-MOD-8 | Journalisation des activations/désactivations (audit) |

---

## 6. Exigences non fonctionnelles

| Réf | Exigence |
|-----|----------|
| ENF-1 | **Isolation** stricte des données par entreprise (déjà en place : `company_id` + scope) |
| ENF-2 | **Performance** : activer/désactiver un module < 2 s ; pages < 300 ms |
| ENF-3 | **Sécurité** : accès aux écrans d'un module conditionné par activation **et** droits du rôle |
| ENF-4 | **Réversibilité** : la désactivation ne supprime pas les données |
| ENF-5 | **Compatibilité** : le socle reste fonctionnel sans aucun module optionnel |
| ENF-6 | **Traçabilité** : audit des activations, paiements, dépassements de quota |

---

## 7. Contraintes de plafonnement (RÈGLES — déjà implémentées)

> Pour éviter la surcharge des tenants et cadrer l'offre.

| Réf | Règle de gestion | État |
|-----|------------------|------|
| RG-1 | **1 owner = 1 tenant** : l'utilisateur qui crée un espace entreprise en est l'unique propriétaire et ne peut pas créer un second espace en tant qu'owner. | ✅ Implémenté (`companies.owner_id`, inscription réservée aux invités) |
| RG-2 | **Maximum 5 employés par tenant**, en plus de l'owner (soit 6 comptes max). | ✅ Implémenté (`Company::MAX_EMPLOYEES`, garde à la création + compteur UI) |
| RG-3 | Le **propriétaire** ne peut être ni supprimé ni rétrogradé. | ✅ Implémenté |
| RG-4 | (Évolution) Le plafond d'employés pourra dépendre de l'**abonnement** (ex. forfait supérieur = plus d'employés). | ⏳ À prévoir |

---

## 8. Architecture cible proposée (orientation conception)

Réutilise et prolonge l'existant (registre `config/modules.php` + rôles par module) :

1. **Déclaration d'un module** (paquet) : clé, libellé, icône, secteur, prix, entités/migrations,
   routes, vues, permissions, données de référence (« seeders » d'activation).
2. **Table `company_modules`** : (`company_id`, `module_key`, `active`, `activated_at`,
   `settings` json) → quels modules sont activés pour chaque tenant.
3. **Résolution d'accès** : un écran de module est visible si **(a)** le module est *activé pour
   le tenant* **et (b)** le *rôle de l'utilisateur y a droit* (mécanisme `canAccessModule` déjà en place).
4. **Activation** : exécute les migrations/données du module (idempotent) si pas déjà fait, crée
   la ligne `company_modules.active=true`.
5. **Désactivation** : masque le module (active=false) **sans** supprimer les données.
6. **Catalogue / Store** : page owner listant les modules disponibles, leur prix et un bouton
   activer/désactiver.

> Cette approche garde le **socle** indépendant : les modules sont des extensions déclaratives.

---

## 9. Modèle économique (orientation)

| Élément | Principe |
|---------|----------|
| Socle | Inclus dans l'abonnement de base |
| Module métier | Option payante (mensuelle) activable/désactivable |
| Quotas | Plafond d'employés et de modules selon le forfait (RG-2, RG-4) |
| Essai | Période d'essai possible par module |

---

## 10. Synthèse des règles de gestion

- **RG-1** 1 owner ↔ 1 tenant. *(fait)*
- **RG-2** ≤ 5 employés par tenant hors owner. *(fait)*
- **RG-3** Owner non supprimable / non rétrogradable. *(fait)*
- **RG-4** Quotas indexés sur l'abonnement. *(à venir)*
- **RG-5** Un module désactivé conserve ses données. *(à concevoir)*
- **RG-6** Visibilité d'un module = activé (tenant) ET autorisé (rôle). *(à concevoir, base présente)*

---

## 11. Lotissement proposé

1. **Lot 0 — Cadre** *(socle prêt)* : plafonds tenant (fait), table `company_modules`, page catalogue, moteur activer/désactiver.
2. **Lot 1 — Bâtiment** : Électricien / Plombier / Peintre (catalogues + fiches d'intervention + attestations) — proche du socle, rapide.
3. **Lot 2 — Opticien** : ordonnances, montures/verres, tiers payant.
4. **Lot 3 — Immobilier** : biens, mandats, baux, quittances.
5. **Lot 4 — Concessionnaire** : véhicules, stock, reprises/financement.
6. **Lot 5 — Facturation des options** : abonnement par module, quotas dynamiques (RG-4).

---

## 12. Glossaire

- **Tenant** : espace d'une entreprise (cloisonné par `company_id`).
- **Owner** : utilisateur propriétaire du tenant (créateur).
- **Socle** : fonctionnalités présentes pour tous les tenants.
- **Module métier (vertical)** : extension optionnelle dédiée à un secteur.
- **Activation** : mise à disposition d'un module pour un tenant donné.
